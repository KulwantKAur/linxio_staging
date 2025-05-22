<?php

namespace App\Service\Notification;


use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Service\Billing\InvoiceService;
use App\Service\Notification\AttachmentHandler\AttachmentInterface;
use App\Service\Notification\AttachmentHandler\InvoiceAttachment;
use Doctrine\ORM\EntityManager;

class AttachmentService
{
    public function __construct(readonly private EntityManager $em, readonly private InvoiceService $invoiceService)
    {
    }

    private function getAttachmentHandler(Event $event): ?AttachmentInterface
    {
        return match ($event->getName()) {
            Event::INVOICE_CREATED,
            Event::PAYMENT_FAILED,
            Event::STRIPE_PAYMENT_FAILED,
            Event::INVOICE_OVERDUE_PARTIALLY_BLOCKED,
            Event::INVOICE_OVERDUE_PARTIALLY_BLOCKED_ADMIN,
            Event::INVOICE_OVERDUE_BLOCKED,
            Event::INVOICE_OVERDUE_BLOCKED_ADMIN,
            => new InvoiceAttachment($this->em, $this->invoiceService),
            default => null,
        };
    }

    public function getAttachments(EventLog $eventLog): array
    {
        return $this->getAttachmentHandler($eventLog->getEvent())?->getAttachments($eventLog) ?? [];
    }
}