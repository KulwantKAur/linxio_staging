<?php

namespace App\Service\Notification\AttachmentHandler;

use App\Entity\EventLog\EventLog;
use App\Entity\Invoice;
use App\Mailer\MailSender;
use App\Service\Billing\InvoiceService;
use Doctrine\ORM\EntityManager;

class InvoiceAttachment implements AttachmentInterface
{
    public function __construct(readonly private EntityManager $em, readonly private InvoiceService $invoiceService)
    {
    }

    public function getAttachments(EventLog $eventLog): array
    {
        $invoice = $this->em->getRepository(Invoice::class)->find($eventLog->getEntityId());
        if (!$invoice) {
            return [];
        }

        $clientHasContract = (bool)$invoice->getClient()->getContractMonths();

        if ($clientHasContract) {
            $attachments = [];
            $invoices = $invoice->getClient()->getOverdueInvoices();
            foreach ($invoices as $invoice) {
                $pdfString = $this->invoiceService->getInvoicePdf($invoice);
                if ($pdfString) {
                    $attachments[] = MailSender::getPdfAttachment(
                        $pdfString, 'Invoice-' . $invoice->getInternalInvoiceId() . '.pdf'
                    );
                }
            }

            return $attachments;
        } else {
            $pdfString = $this->invoiceService->getInvoicePdf($invoice);
            if (!$pdfString) {
                return [];
            }

            return [MailSender::getPdfAttachment($pdfString, 'Invoice-' . $invoice->getInternalInvoiceId() . '.pdf')];
        }
    }
}