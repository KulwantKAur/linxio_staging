<?php

namespace App\Service\Notification\Placeholder\EntityHandler;

use App\Entity\BaseEntity;
use App\Entity\Invoice;
use App\Entity\Notification\Template;
use App\Entity\Team;
use App\Entity\User;
use App\Service\Notification\Placeholder\AbstractEntityHandlerDecorator;
use App\Util\DateHelper;

class InvoiceEntityHandler extends AbstractEntityHandlerDecorator
{
    /** @var Invoice */
    protected $entity;

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->entity->getClient()->getTeam();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getValueHandlerPlaceholder(?User $user = null): array
    {
        $data = [
            'fromCompany' => $this->getFromCompany() ?? null,
            'team' => $this->getTeamName(),
            'invoiceId' => $this->entity->getInternalInvoiceId(),
            'invoiceLink' => $this->getInvoiceFrontendLink(),
            'eventTime' => DateHelper::formatDate(
                new \DateTime(),
                $user?->getDateFormatSettingConverted(true)
                ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                $user?->getTimezone() ?? $this->entity->getClient()?->getTimeZone()?->getName()
            ),
            'message' => $this->getContext()['message'] ?? '',
            'fullName' => $this->entity->getClient()->getAccountingContact()?->getFullName()
                ?? $this->entity->getClient()->getKeyContact()?->getFullName(),
            'dueDate' => DateHelper::formatDate(
                $this->entity->getDueAt(),
                BaseEntity::EXPORT_DATE_WITHOUT_TIME_FORMAT,
                $user?->getTimezone() ?? $this->entity->getClient()->getTimeZoneName()
            ),
            'total' => $this->entity->getTotalWithPrepayment(),
            'errorDetails' => $this->getErrorDetails()
        ];

        $data['customText'] = $this->getCustomText($data, $user);

        return $data;
    }

    protected function getInvoiceFrontendLink(): ?string
    {
        $invoiceId = $this->entity?->getId();

        return $invoiceId
            ? vsprintf(
                '%s: %s/client/billing/transactions/%d',
                [
                    $this->translator->trans('invoice_page', [], Template::TRANSLATE_DOMAIN),
                    $this->getAppFrontUrl(),
                    $invoiceId
                ]
            )
            : null;
    }

    protected function getCustomText(array $data, ?User $user)
    {
        $clientHasContract = (bool)$this->entity->getClient()->getContractMonths();
        $customTextTpl = $clientHasContract ? 'INVOICE_OVERDUE_PARTIALLY_BLOCKED_CONTRACT' : 'INVOICE_OVERDUE_PARTIALLY_BLOCKED_NO_CONTRACT';

        $data['${from_company}'] = $this->getFromCompany() ?? null;
        $data['${invoice_id}'] = $this->entity->getInternalInvoiceId();
        $data['${due_date}'] = DateHelper::formatDate(
            $this->entity->getDueAt(),
            BaseEntity::EXPORT_DATE_WITHOUT_TIME_FORMAT,
            $user?->getTimezone() ?? $this->entity->getClient()->getTimeZoneName()
        );
        $data['${total}'] = $this->entity->getTotalWithPrepayment();

        if ($clientHasContract) {
            $invoices = $this->entity->getClient()->getOverdueInvoices();

            $data['${invoices}'] = '';
            foreach ($invoices as $invoice) {
                $data['${invoices}'] .= $this->translator->trans('INVOICE_OVERDUE_BLOCKED_INVOICE', [
                    '${invoice_id}' => $invoice->getInternalInvoiceId(),
                    '${due_date}' => DateHelper::formatDate(
                        $invoice->getDueAt(),
                        BaseEntity::EXPORT_DATE_WITHOUT_TIME_FORMAT,
                        $user?->getTimezone() ?? $invoice->getClient()->getTimeZoneName()
                    ),
                    '${total}' => $invoice->getTotalWithPrepayment()
                ], Template::TRANSLATE_DOMAIN);
            }
        }

        return $this->translator->trans($customTextTpl, $data, Template::TRANSLATE_DOMAIN);
    }

    protected function getErrorDetails()
    {
        $messages = $this->getContext()['message'] ?? [];

        return is_string($messages) ? $messages : implode(' ', $messages);
    }
}
