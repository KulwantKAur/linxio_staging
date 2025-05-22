<?php

namespace App\Service\Xero;

use App\Entity\Client;
use App\Entity\Notification\Event;
use App\Entity\Plan;
use App\Exceptions\ValidationException;
use App\Repository\Billing\ClientBilling;
use XeroAPI\XeroPHP\ApiException;
use XeroAPI\XeroPHP\Models\Accounting\Account;
use XeroAPI\XeroPHP\Models\Accounting\Contact;
use XeroAPI\XeroPHP\Models\Accounting\Invoice;
use XeroAPI\XeroPHP\Models\Accounting\Invoices;
use XeroAPI\XeroPHP\Models\Accounting\LineItem;
use XeroAPI\XeroPHP\Models\Accounting\Payment;
use XeroAPI\XeroPHP\Models\Accounting\Payments;

class XeroInvoiceService extends XeroService
{
    /** @var Contact */
    private $contact;

    /** @var LineItem[] */
    private $lineItems;

    /**
     * @param \App\Entity\Invoice $invoice
     * @return \XeroAPI\XeroPHP\Models\Accounting\Error|Invoices
     * @throws \XeroAPI\XeroPHP\ApiException
     */
    public function generateFromInvoice(\App\Entity\Invoice $invoice)
    {
        $rows = [];
        $details = $invoice->toArray(['details'])['details'];

        if (!$invoice->getClient()->getXeroClientAccount()) {
            return;
        }

        if (!$details && !$invoice->getPreviousPrepayment() && !$invoice->getPrepayment()) {
            throw new \Exception('No items for generate invoice');
        }
        foreach ($details as $row) {
            //Skip rows with zero price
            if (!isset($row['total']) || $row['total'] == 0) {
                continue;
            }

            $rows[] = [
                'item_code' => $this->getItemCodeWithPostfix($invoice, $row['key']),
                'description' => $this->translator->trans('billing.' . $row['key'], [], 'entities'),
                'quantity' => $row['quantity'],
                'price' => $row['price'],
                'account_id' => $invoice->getOwnerTeam()->getXeroClientSecret()->getXeroAccountLineitemId(),
            ];
        }

        if ($invoice->getPreviousPrepayment()) {
            $invoiceDate = $invoice->getPreviousPrepayment()->getPeriodStart();

            $rows[] = [
                'description' => $this->translator->trans('billing.prepayment', [
                    '%month%' => $this->translator->trans('calendar.months.month_' . $invoiceDate->format('n')),
                    '%year%' => $invoiceDate->format('Y')
                ], 'entities'),
                'quantity' => 1,
                'price' => -1 * $invoice->getPreviousPrepayment()->getAmount(),
                'account_id' => $invoice->getOwnerTeam()->getXeroClientSecret()->getXeroAccountLineitemId(),
            ];
        }

        if ($invoice->getPrepayment()) {
            $invoiceDate = $invoice->getPrepayment()->getPeriodStart();
            $rows[] = [
                'description' => $this->translator->trans('billing.prepayment', [
                    '%month%' => $this->translator->trans('calendar.months.month_' . $invoiceDate->format('n')),
                    '%year%' => $invoiceDate->format('Y')
                ], 'entities'),
                'quantity' => 1,
                'price' => $invoice->getPrepayment()->getAmount(),
                'account_id' => $invoice->getOwnerTeam()->getXeroClientSecret()->getXeroAccountLineitemId(),
            ];
        }

        $this->setUserTeam($invoice->getOwnerTeam());

        if (empty($rows)) {
            throw new \Exception('No items for generate invoice');
        }

        return $this->generate(
            $invoice->getId(),
            $invoice->getClient(),
            $rows,
            $invoice->getPeriodStart(),
            $invoice->getDueAt(),
            $invoice->getOwnerTeam()->getXeroClientSecret()->getXeroTenantId()
        );
    }

    /**
     * @param $invoiceId
     * @param $contactId
     * @param $rows
     * @param $date
     * @param $dueDate
     * @param $tenantId
     * @return \XeroAPI\XeroPHP\Models\Accounting\Error|Invoices
     * @throws \XeroAPI\XeroPHP\ApiException
     */
    private function generate($invoiceId, Client $client, $rows, $date, $dueDate, $tenantId)
    {
        $this->setContact($client->getXeroClientAccount()->getXeroContactId());
        $this->setLineItems($rows);

        $invoice = new Invoice();
        $invoice->setType(Invoice::TYPE_ACCREC);
        $invoice->setContact($this->contact);
        $invoice->setInvoiceNumber('LX-' . $client->getId() . '-' . $invoiceId);
        $invoice->setDate($date);
        $invoice->setDueDate($dueDate);
        $invoice->setLineItems($this->lineItems);
        $invoice->setReference('Linxio invoice');
        $invoice->setStatus(Invoice::STATUS_AUTHORISED);

        $invoices = new Invoices();
        $invoices->setInvoices([$invoice]);

        try {
            $apiInstance = $this->getAccountingApi();

            $result = $apiInstance->createInvoices($tenantId, $invoices);

            $errors = $this->getErrors($result);
            if (!empty($errors)) {
                throw (new ValidationException())->setErrors($errors);
            }

            return $result;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    private function setContact($id)
    {
        $this->contact = new Contact();
        $this->contact->setContactID($id);
    }

    private function setLineItems($lineItems)
    {
        $this->lineItems = [];
        foreach ($lineItems as $item) {
            $this->lineItems[] = $this->createLineItem($item);
        }
    }

    private function createLineItem($item)
    {
        $lineItem = new LineItem();
        $lineItem->setDescription($item['description']);
        $lineItem->setQuantity($item['quantity']);
        $lineItem->setUnitAmount($item['price']);
        if (isset($item['account_id'])) {
            $lineItem->setAccountId($item['account_id']);
        }

        if (isset($item['item_code'])) {
            $lineItem->setItemCode($item['item_code']);
        }

        return $lineItem;
    }

    public function getItemCodes()
    {
        return [
            ClientBilling::activeVehicleTrackersAlias => 'AD-V',
            ClientBilling::deactivatedVehicleTrackersAlias => 'DD-V',

            ClientBilling::activePersonalTrackersAlias => 'AD-P',
            ClientBilling::deactivatedPersonalTrackersAlias => 'DD-P',

            ClientBilling::activeAssetTrackersAlias => 'AD-A',
            ClientBilling::deactivatedAssetTrackersAlias => 'DD-A',

            ClientBilling::activeSatelliteTrackersAlias => 'AD-S',
            ClientBilling::deactivatedSatelliteTrackersAlias => 'DD-S',

            ClientBilling::activeVehiclesAlias => 'AV',
            ClientBilling::virtualVehiclesAlias => 'VV',
            ClientBilling::archivedVehiclesAlias => 'ARV',
            //ClientBilling::deletedVehiclesAlias => 'DV',

            ClientBilling::activeSensorsAlias => 'AS',
            ClientBilling::archivedSensorsAlias => 'ARS',
            //ClientBilling::deletedSensorsAlias => 'DS',
        ];
    }

    public function getItemCodeWithPostfix(\App\Entity\Invoice $invoice, string $alias): ?string
    {
        if (!isset($this->getItemCodes()[$alias])) {
            return null;
        }

        $withPostfix = [
            ClientBilling::activeVehicleTrackersAlias,
            ClientBilling::activePersonalTrackersAlias,
            ClientBilling::activeAssetTrackersAlias,
            ClientBilling::activeSatelliteTrackersAlias,
        ];

        if (!in_array($alias, $withPostfix)) {
            return $this->getItemCodes()[$alias];
        }

        $postifix = match ($invoice->getClient()->getPlan()->getName()) {
            Plan::PLAN_STARTER => '-S',
            Plan::PLAN_ESSENTIALS => '-E',
            Plan::PLAN_PLUS => '-P',
            default => '',
        };

        return $this->getItemCodes()[$alias] . $postifix;
    }

    /**
     * @param $alias
     * @return string
     */
    public function getItemCodeByAlias($alias)
    {
        $codes = $this->getItemCodes();

        return $codes[$alias] ?? null;
    }

    public function getErrors($result)
    {
        $errors = [];
        if ($result->count()) {
            $invoice = $result->getInvoices()[0];
            if ($invoice->getHasErrors()) {
                foreach ($invoice->getValidationErrors() as $error) {
                    $errors[] = $error->getMessage();
                }
            }
        }

        return $errors;
    }

    public function createPayment(\App\Entity\Invoice $invoice)
    {
        if (!$invoice->getExtInvoiceId()) {
            return;
        }

        if (!$invoice->getOwnerTeam()->getXeroClientSecret()) {
            return;
        }
        $this->setUserTeam($invoice->getOwnerTeam());
        $apiInstance = $this->getAccountingApi();
        $xeroClient = $invoice->getOwnerTeam()->getXeroClientSecret();

        $payment = new Payment([
            'invoice' => ['InvoiceID' => $invoice->getExtInvoiceId()],
            'amount' => $invoice->getTotalWithPrepayment(),
            'account' => (new Account())->setAccountId($xeroClient->getXeroAccountPaymentId()),
            'date' => date('Y-m-d'),
        ]);

        try {
            $payment = $apiInstance->createPayment($xeroClient->getXeroTenantId(), $payment);
            $this->notificationDispatcher->dispatch(Event::XERO_PAYMENT_CREATED, $invoice);

            return $payment;
        } catch (ApiException $exception) {
            if (isset($exception->getResponseObject()?->getElements()[0])) {
                $errors = $exception->getResponseObject()->getElements()[0]->getValidationErrors();
                throw (new ValidationException())->setErrors($errors);
            } else {
                throw $exception;
            }
        }
    }

    public function getAccounts($tenantId)
    {
        $where = sprintf('Status=="%s"', Account::STATUS_ACTIVE);
        $order = "Name ASC";

        $accounts = $this->getAccountingApi()->getAccounts($tenantId, null, $where, $order);

        $result = [];

        /** @var Account $account */
        foreach ($accounts->getAccounts() as $account) {
            $result[] = [
                'name' => $account->getName(),
                'code' => $account->getCode(),
                'description' => $account->getDescription(),
                'class' => $account->getClass(),
                'id' => $account->getAccountId()
            ];
        }

        return $result;
    }

    public function getInvoice($tenantId, $invoiceId)
    {
        $invoice = $this->getAccountingApi()->getInvoice($tenantId, $invoiceId);

        return $invoice->count() ? $invoice->getInvoices()[0] : [];
    }

    public function syncStatusFromXero(\App\Entity\Invoice $invoice)
    {
        if (!$invoice->getExtInvoiceId()) {
            return null;
        }
        $this->setUserTeam($invoice->getOwnerTeam());
        $xeroInvoice = $this->getInvoice($invoice->getOwnerTeam()->getXeroClientSecret()->getXeroTenantId(),
            $invoice->getExtInvoiceId());

        if ($xeroInvoice->getStatus() == Invoice::STATUS_PAID) {
            $invoice->setExtPaid(true);
            if ($xeroInvoice->getPayments()) {
                $invoice->setExtPaymentId($xeroInvoice->getPayments()[0]->getPaymentId());
            }
            $invoice->setStatus(\App\Entity\Invoice::STATUS_PAID);
            $this->em->persist($invoice);
            $this->em->flush();

            return $invoice;
        }

        return null;
    }

    public function syncStatusToXero(\App\Entity\Invoice $invoice)
    {
        $payment = $this->createPayment($invoice);
        $this->linkXeroPaymentToInvoice($payment, $invoice);
    }

    public function linkXeroInvoiceToInvoice(Invoices $xeroInvoices, \App\Entity\Invoice $invoice)
    {
        $invoices = $xeroInvoices->getInvoices();
        if (!isset($invoices[0])) {
            throw new \Exception('Something went wrong');
        }
        $invoice->setExtInvoiceId($invoices[0]->getInvoiceId());
        $this->em->persist($invoice);
        $this->em->flush();

        return $invoice;
    }

    public function linkXeroPaymentToInvoice(?Payments $payments, \App\Entity\Invoice $invoice)
    {
        if ($payments && $payments->count()) {
            $invoice->setExtPaymentId($payments->getPayments()[0]->getPaymentId());
            $this->em->flush();
        }
    }
}
