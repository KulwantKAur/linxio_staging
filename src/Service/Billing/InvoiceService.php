<?php

namespace App\Service\Billing;

use App\Entity\AdminTeamInfo;
use App\Entity\Client;
use App\Entity\Invoice;
use App\Entity\InvoiceDetails;
use App\Entity\Notification\Event;
use App\Entity\Team;
use App\Repository\Billing\ClientBilling;
use App\Service\BaseService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\PdfService;
use App\Service\PlatformSetting\PlatformSettingService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Contracts\Translation\TranslatorInterface;

class InvoiceService extends BaseService
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManager $em,
        private readonly NotificationEventDispatcher $notificationDispatcher,
        readonly private PlatformSettingService $platformSettingService,
        readonly private PdfService $pdfService
    ) {
    }

    /**
     * @param $clientId
     * @param $filters
     * @return mixed
     */
    public function getListForUser($clientId, $filters = [])
    {
        return $this->em->getRepository(Invoice::class)->getListByClients($clientId, $filters);
    }

    /**
     * @param $invoiceId
     * @return Invoice
     */
    public function getDetails($invoiceId)
    {
        return $this->em->getRepository(Invoice::class)->find($invoiceId);
    }

    public function getAvailableKeys()
    {
        return [
            ClientBilling::activeVehicleTrackersAlias,
            ClientBilling::deactivatedVehicleTrackersAlias,

            ClientBilling::activePersonalTrackersAlias,
            ClientBilling::deactivatedPersonalTrackersAlias,

            ClientBilling::activeAssetTrackersAlias,
            ClientBilling::deactivatedAssetTrackersAlias,

            ClientBilling::activeSatelliteTrackersAlias,
            ClientBilling::deactivatedSatelliteTrackersAlias,

            ClientBilling::activeVehiclesAlias,
            ClientBilling::virtualVehiclesAlias,
            ClientBilling::archivedVehiclesAlias,
            //ClientBilling::deletedVehiclesAlias,

            ClientBilling::activeSensorsAlias,
            ClientBilling::archivedSensorsAlias,
            //ClientBilling::deletedSensorsAlias,
        ];
    }

    public function exists($items, $startDate, $endDate)
    {
        return $this->em->getRepository(Invoice::class)->exists($items['client_id'], $startDate, $endDate);
    }

    private function billingToInvoice(array $items, $startDate, $endDate)
    {
        $connection = $this->em->getConnection();
        $total = 0;
        try {
            $connection->beginTransaction();
            /** @var Client $client */
            $client = $this->em->getReference(Client::class, $items['client_id']);
            $invoice = new Invoice([
                'period_start' => $startDate,
                'period_end' => $endDate,
                'due_at' => Carbon::create($endDate)
                    ->addDays($client->getInvoiceDueDays() ?? Client::INVOICE_DUE_DAYS),
                'client' => $client,
                'prepayment' => isset($items['prepayment_id']) ?
                    $this->em->getReference(Invoice::class, $items['prepayment_id']) : null,
                'previous_prepayment' => isset($items['previous_prepayment_id'])
                    ? $this->em->getReference(Invoice::class, $items['previous_prepayment_id']) : null,
                'type' => $items['type'],
            ]);

            $this->em->persist($invoice);
            $this->em->flush();

            $invoiceLineItems = [];
            $keys = $this->getAvailableKeys();

            if ($client->getSignPostSettingValue()) {
                $keys[] = ClientBilling::active_vehicle_sign_post;
            }
            foreach ($keys as $key) {
                if (
                    array_key_exists($key, $items)
                    && array_key_exists($key . '_price', $items)
                    && array_key_exists($key . '_total', $items)
                ) {
                    $invoiceDetails = new InvoiceDetails([
                        'invoice' => $invoice,
                        'key' => $key,
                        'quantity' => $items[$key],
                        'price' => $items[$key . '_price'],
                        'total' => $items[$key . '_total'],
                    ]);

                    $total += $items[$key . '_total'];

                    $this->em->persist($invoiceDetails);

                    $invoiceLineItems[] = $invoiceDetails;
                }
            }

            $invoice->setTax($invoice->calculateTax($invoiceLineItems));
            $invoice->setAmount($total);

            $this->em->persist($invoice);
            $this->em->flush();

            $connection->commit();

            if ($invoice->getType() === Invoice::TYPE_REGULAR) {
                $this->notificationDispatcher->dispatch(Event::INVOICE_CREATED, $invoice);
            }

            return $invoice;
        } catch (\Exception $exception) {
            $this->em->getConnection()->rollback();
            throw $exception;
        }
    }

    /**
     * @param array $results
     * @param array $fields
     * @return array
     */
    public function prepareExportData($results, array $fields = [])
    {
        return $this->translateEntityArrayForExport($results, $fields, Invoice::class);
    }

    /**
     * @param $id
     * @return Invoice
     * @throws EntityNotFoundException
     */
    public function getById($id)
    {
        /** @var Invoice $invoice */
        $invoice = $this->em->getRepository(Invoice::class)->find($id);

        if (!$invoice) {
            throw new EntityNotFoundException($this->translator->trans('entities.invoice.not_found'));
        }

        if ($invoice->getStatus() !== Invoice::STATUS_PAID) {
            throw new \Exception($this->translator->trans('entities.invoice.xero.not_paid'));
        }

        return $invoice;
    }

    public function createPrepayment($invoiceItems, $prepaymentItems, $startDate, $endDate)
    {
        $prepaymentItems = $prepaymentItems[$invoiceItems['team_id']];
        $prepaymentItems['type'] = Invoice::TYPE_PREPAYMENT;

        return $this->billingToInvoice($prepaymentItems, $startDate, $endDate);
    }

    public function createInvoice(
        $invoiceItems,
        ?Invoice $prepaymentInvoice,
        $startDate,
        $endDate,
        $withPrepayment = true
    ) {
        $previousPrepayment = $this->em->getRepository(Invoice::class)
            ->getPreviousPrepayment($invoiceItems['client_id'], $prepaymentInvoice);

        if ($withPrepayment) {
            $invoiceItems['prepayment_id'] = $prepaymentInvoice->getId();
        }
        if ($previousPrepayment) {
            $invoiceItems['previous_prepayment_id'] = $previousPrepayment->getId();
        }

        $invoiceItems['type'] = Invoice::TYPE_REGULAR;

        return $this->billingToInvoice($invoiceItems, $startDate, $endDate);
    }

    public function isNewClient($clientId)
    {
        return !$this->em->getRepository(Invoice::class)->haveInvoices($clientId);
    }

    public function getInvoicePdf(Invoice $invoice): bool|string
    {
        $adminTeam = $this->em->getRepository(Team::class)->findOneBy(['type' => Team::TEAM_ADMIN]);
        $adminInfo = $this->em->getRepository(AdminTeamInfo::class)->findOneBy(['team' => $adminTeam]);
        $platformSetting = $invoice->getTeam()->getPlatformSettingByTeam();

        return $this->pdfService->getInvoicePdf($invoice, $adminInfo, $platformSetting);
    }

    public function cleanNotSyncedInvoices(Client $client)
    {
        $invoicesRegular = $this->em->getRepository(Invoice::class)
            ->findWithoutXero($client, Invoice::TYPE_REGULAR);

        /** @var Invoice $regular */
        foreach ($invoicesRegular as $regular) {
            $this->em->remove($regular);
        }

//        $invoicesPrepayment = $this->em->getRepository(Invoice::class)
//            ->findWithoutXero($client, Invoice::TYPE_PREPAYMENT);
//
//        /** @var Invoice $invoice */
//        foreach ($invoicesPrepayment as $prepayment) {
//            $invoiceOfPrepayment = $this->em->getRepository(Invoice::class)
//                ->findOneBy(['prepayment' => $prepayment]);
//            if (!$invoiceOfPrepayment) {
//                $this->em->remove($prepayment);
//            }
//        }

        $this->em->flush();
    }
}

