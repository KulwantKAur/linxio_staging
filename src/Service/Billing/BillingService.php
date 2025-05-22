<?php

namespace App\Service\Billing;

use App\Entity\BillingSetting;
use App\Entity\Client;
use App\Entity\EventLog\EventLog;
use App\Entity\Invoice;
use App\Entity\Notification\Event;
use App\Entity\PlatformSetting;
use App\Entity\Team;
use App\Entity\User;
use App\Repository\Billing\ClientBilling;
use App\Service\Billing\DTO\BillingInfoDTO;
use App\Util\TranslateHelper;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BillingService
{
    private PaginatorInterface $paginator;
    private EntityManagerInterface $em;
    private TranslatorInterface $translator;

    public function __construct(PaginatorInterface $paginator, EntityManager $em, TranslatorInterface $translator)
    {
        $this->paginator = $paginator;
        $this->em = $em;
        $this->translator = $translator;
    }

    public function updateBillingItemsFormat(array $items): array
    {
        return array_map(function ($item) {
            $filtered = array_filter(
                $item,
                fn($key) => !in_array($key, ClientBilling::getTotalSumFields()),
                ARRAY_FILTER_USE_KEY
            );

            foreach ($filtered as $key => $value) {
                $filtered[$key] = in_array($key, ['client_name']) ? $value : floatval($value);
            }

            return $filtered;
        }, $items);
    }

    public function getTotalData(array $data)
    {
        if (!$data) {
            return [];
        }

        $filtered = array_filter(
            $data[0],
            fn($key) => in_array($key, ClientBilling::getTotalSumFields()),
            ARRAY_FILTER_USE_KEY
        );

        return array_map(fn($value) => floatval($value), $filtered);
    }

    public function getClientsBillingPayments(array $params, User $currentUser): QueryBuilder
    {
        $paramsDTO = new BillingInfoDTO($params, $currentUser);

        return $this->em->getRepository(Client::class)->getClientsBillingPaymentInfo($paramsDTO);
    }

    public function getClientsBillingInfo(BillingInfoDTO $params): SlidingPaginationInterface
    {
        $query = $this->em->getRepository(Client::class)->getClientsBillingInfo($params);

        return $this->paginator->paginate($query, $params->page, $params->limit);
    }

    public function prepareExportData(array $results, array $fields = [])
    {
        return TranslateHelper::translateEntityArrayForExport(
            $results, $this->translator, $fields, 'billing'
        );
    }

    public function grossChart(array $params, User $user)
    {
        $paramsDTO = new BillingInfoDTO($params, $user);
        $paramsDTO->sort = null;
        $startDate = (new Carbon($paramsDTO->startDate));
        $endDate = (new Carbon($paramsDTO->endDate));
        $data = [];
        $method = match ($paramsDTO->period) {
            'week' => 'addWeek',
            'month' => 'addMonth',
            default => 'addDay',
        };

        while ($startDate < $endDate) {
            $paramsDTO->startDate = (clone $startDate)->setTimezone('UTC');
            $paramsDTO->endDate = (clone $startDate)->{$method}()->setTimezone('UTC');
            $data[] = [
                'value' => $this->em->getRepository(Client::class)->getGrossChartData($paramsDTO),
                'startDate' => (clone $startDate)->setTimezone('UTC')->format('c'),
                'endDate' => (clone $startDate)->{$method}()->setTimezone('UTC')->format('c'),
            ];
            $startDate->{$method}();
        }

        return $data;
    }

    public function topClientsChart(array $params, User $user)
    {
        $paramsDTO = new BillingInfoDTO($params, $user);
        $paramsDTO->sort = null;

        return $this->em->getRepository(Client::class)->getTopClientsChartData($paramsDTO);
    }

    /**
     * @param array $params
     * @param User $currentUser
     * @return SlidingPaginationInterface
     */
    public function getClientsBillingForPeriod(array $params, User $currentUser): SlidingPaginationInterface
    {
        $paramsDTO = new BillingInfoDTO($params, $currentUser);
        $startDate = $paramsDTO->startDate;
        $endDate = $paramsDTO->endDate;
        $startDateStartOfMonth = Carbon::parse($startDate)->startOfMonth()->format('Y-m-d');
        $endDateEndOfMonth = Carbon::parse($endDate)->endOfMonth()->format('Y-m-d');
        $period = CarbonPeriod::create($startDateStartOfMonth, '1 month', $endDateEndOfMonth);
        $pagination = '';
        foreach ($period as $dt) {
            $startOfMonth = $dt->startOfMonth()->format('Y-m-d');
            $endOfMonth = $dt->endOfMonth()->format('Y-m-d');
            $paramsDTO->startDate = $startOfMonth > $startDate ? $startOfMonth : $startDate;
            $paramsDTO->endDate = $endOfMonth < $endDate ? $endOfMonth : $endDate;
            $paginationCurrent = $this->getClientsBillingInfo($paramsDTO);
            if ($pagination) {
                $pagination = $this->mergePaginations($pagination, $paginationCurrent);
            } else {
                $pagination = $paginationCurrent;
            }
        }

        return $pagination;
    }

    /**
     * @param SlidingPaginationInterface $pagination
     * @param SlidingPaginationInterface $paginationCurrent
     * @return SlidingPaginationInterface
     */
    public function mergePaginations(
        SlidingPaginationInterface $pagination,
        SlidingPaginationInterface $paginationCurrent
    ): SlidingPaginationInterface {
        $itemsPagination = $pagination->getItems();
        $itemsPaginationCurrent = $paginationCurrent->getItems();
        foreach ($itemsPagination as $key => $item) {
            if ($itemsPagination[$key]['team_id'] != $itemsPaginationCurrent[$key]['team_id']) {
                continue;
            }
            $filtered = array_filter(
                $itemsPaginationCurrent[$key],
                fn($key) => in_array($key, ClientBilling::getAliasFields()),
                ARRAY_FILTER_USE_KEY
            );
            foreach ($filtered as $key1 => $val1) {
                $itemsPagination[$key][$key1] += $val1;
            }
        }
        $pagination->setItems($itemsPagination);

        return $pagination;
    }

    public function getClientsBillingForPrepayment(array $params, User $currentUser): QueryBuilder
    {
        $paramsDTO = new BillingInfoDTO($params, $currentUser);

        return $this->em->getRepository(Client::class)->getClientsBillingPrepayment($paramsDTO);
    }

    public function getAcountStatus(Client $client)
    {
        $haveOverdue = $this->em->getRepository(Invoice::class)->haveOverdueByClientId($client->getId());
        $haveNotPaid = $this->em->getRepository(Invoice::class)->haveNotPaidByClientId($client->getId());
        /** @var Invoice $lastFailedPayment */
        $lastFailedPayment = $this->em->getRepository(Invoice::class)->lastFailedPaymentByClientId($client->getId());

        $paymentMessage = '';

        if ($haveOverdue) {
            $invoicesMessage = $this->translator->trans('billing.accountStatus.haveOverdueInvoices');
        } else {
            if ($haveNotPaid) {
                $invoicesMessage = $this->translator->trans('billing.accountStatus.haveNotPaidInvoices');
            } else {
                $invoicesMessage = 'OK';
                $paymentMessage = $this->translator->trans('billing.accountStatus.noPendingInvoices');
            }
        }

        if (($haveOverdue || $haveNotPaid) && $client->isManualPayment()) {
            $paymentMessage = $this->translator->trans('billing.accountStatus.checkPaymentMethod');
        }

        if ($lastFailedPayment) {
            $event = $this->em->getRepository(Event::class)->findOneBy(['name' => Event::STRIPE_PAYMENT_FAILED]);
            /** @var EventLog $eventLog */
            $eventLog = $this->em->getRepository(EventLog::class)->getLastEventLogByDetailsId($event,
                $lastFailedPayment->getId());
            $paymentMessage = $this->translator->trans('billing.accountStatus.paymentError',
                ['%error%' => $eventLog->getDetails()['context']['message'] ?? '']);
        }

        return [
            'invoicesMessage' => $invoicesMessage,
            'paymentMessage' => $paymentMessage,
            'type' => match (true) {
                (bool)$lastFailedPayment, $haveOverdue => 'error',
                $haveNotPaid => 'warning',
                default => 'good',
            }
        ];
    }

    public function createBillingSetting(array $data, Team $team): BillingSetting
    {
        $billingSetting = new BillingSetting($data);
        $billingSetting->setTeam($team);

        $this->em->persist($billingSetting);
        $this->em->flush();

        return $billingSetting;
    }

    public function getBillingSettingByTeam(Team $team): ?BillingSetting
    {
        if ($team->isClientTeam() && $team->getClient()->getReseller()) {
            if ($team->isAdminTeam() || $team->isResellerTeam()) {
                return $team->getBillingSetting();
            }
            if ($team->getClient()?->getReseller()) {
                return $team->getClient()->getReseller()->getTeam()->getBillingSetting();
            }
        } elseif ($team->isAdminTeam() || $team->isResellerTeam()) {
            return $team->getBillingSetting();
        } else {
            $adminTeam = $this->em->getRepository(Team::class)->findOneBy(['type' => Team::TEAM_ADMIN]);

            return $adminTeam?->getBillingSetting();
        }

        return null;
    }
}