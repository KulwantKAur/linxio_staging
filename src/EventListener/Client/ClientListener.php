<?php

namespace App\EventListener\Client;

use App\Entity\BillingEntityHistory;
use App\Entity\Client;
use App\Entity\EntityHistory;
use App\Entity\Invoice;
use App\Enums\EntityHistoryTypes;
use App\Events\Client\ClientCheckPaidInvoicesEvent;
use App\Events\Client\ClientContractChangedEvent;
use App\Events\Client\ClientCreatedEvent;
use App\Events\Client\ClientStatusChangedEvent;
use App\Events\Client\ClientUpdatedEvent;
use App\Service\Billing\BillingEntityHistoryService;
use App\Service\EntityHistory\EntityHistoryService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClientListener implements EventSubscriberInterface
{
    private $em;
    private $entityHistoryService;
    private BillingEntityHistoryService $billingEntityHistoryService;

    /**
     * ClientListener constructor.
     * @param EntityManagerInterface $em
     * @param EntityHistoryService $entityHistoryService
     */
    public function __construct(
        EntityManagerInterface $em,
        EntityHistoryService $entityHistoryService,
        BillingEntityHistoryService $billingEntityHistoryService
    ) {
        $this->em = $em;
        $this->entityHistoryService = $entityHistoryService;
        $this->billingEntityHistoryService = $billingEntityHistoryService;
    }

    public static function getSubscribedEvents()
    {
        return [
            ClientStatusChangedEvent::NAME => 'onClientStatusChanged',
            ClientCreatedEvent::NAME => 'onClientCreated',
            ClientUpdatedEvent::NAME => 'onClientUpdated',
            ClientCheckPaidInvoicesEvent::NAME => 'onClientCheckPaidInvoices',
            ClientContractChangedEvent::NAME => 'onClientContractChanged',
        ];
    }

    /**
     * @param ClientUpdatedEvent $event
     */
    public function onClientUpdated(ClientUpdatedEvent $event)
    {
        $client = $event->getClient();
        $client->setUpdatedAt(Carbon::now('UTC'));
        $this->entityHistoryService->create(
            $client,
            $client->getUpdatedAt()->getTimestamp(),
            EntityHistoryTypes::CLIENT_UPDATED,
            $client->getCreatedBy()
        );
        $this->entityHistoryService->create($client, $client->getStatus(), EntityHistoryTypes::CLIENT_STATUS);

        $this->em->flush();
    }

    /**
     * @param ClientCreatedEvent $event
     */
    public function onClientCreated(ClientCreatedEvent $event)
    {
        $client = $event->getClient();
        $this->entityHistoryService->create(
            $client,
            $client->getCreatedAt()->getTimestamp(),
            EntityHistoryTypes::CLIENT_CREATED,
            $client->getCreatedBy()
        );

        $this->em->flush();
    }

    public function onClientStatusChanged(ClientStatusChangedEvent $event)
    {
        $client = $event->getClient();

        $this->billingEntityHistoryService->closeAndCreateRecord(
            $client->getId(),
            BillingEntityHistory::ENTITY_CLIENT,
            BillingEntityHistory::TYPE_CHANGE_STATUS,
            $client->getTeam(),
            ['status' => $client->getStatus()]
        );
    }

    public function onClientContractChanged(ClientContractChangedEvent $event)
    {
        $client = $event->getClient();
        $clientOld = $event->getClientOld();

        if (($clientOld && $clientOld->getContractMonths() !== $client->getContractMonths())
            || (is_null($clientOld) && $client->getContractMonths())) {
            $this->entityHistoryService->create(
                $client,
                $client->getContractMonths() ?? '',
                EntityHistoryTypes::CLIENT_CONTRACT_CHANGED,
                $client->getUpdatedBy()
            );
        }

        $this->em->flush();
    }

    public function onClientCheckPaidInvoices(ClientCheckPaidInvoicesEvent $checkPaidInvoicesEvent)
    {
        $client = $checkPaidInvoicesEvent->getClient();
        if (!in_array($client->getStatus(),
            [Client::STATUS_PARTIALLY_BLOCKED_BILLING, Client::STATUS_BLOCKED_BILLING])) {
            return;
        }

        $overDueInvoices = $this->em->getRepository(Invoice::class)->findOverdueInvoicesByClientTeam($client->getTeam()->getId());
        if (!count($overDueInvoices)) {
            /** @var EntityHistory $lastClientStatus */
            $lastClientStatus = $this->entityHistoryService->getLastByEntityAndEntityIdAndType(
                Client::class, $client->getId(), EntityHistoryTypes::CLIENT_STATUS
            );

            if ($lastClientStatus && !in_array($lastClientStatus->getPayload(),
                    [Client::STATUS_PARTIALLY_BLOCKED_BILLING, Client::STATUS_BLOCKED_BILLING])) {
                $client->setStatus($lastClientStatus->getPayload());
            } else {
                $client->setStatus(Client::STATUS_CLIENT);
            }

            $this->em->flush();
        }
    }
}