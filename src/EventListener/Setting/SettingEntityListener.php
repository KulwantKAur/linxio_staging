<?php

namespace App\EventListener\Setting;

use App\Entity\Client;
use App\Entity\Setting;
use App\Enums\EntityHistoryTypes;
use App\Service\EntityHistory\EntityHistoryService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class SettingEntityListener
{
    public function __construct(
        private readonly EntityHistoryService $entityHistoryService,
        private readonly EntityManager $em
    ) {
    }

    private function handleBilling(Setting $setting, PreUpdateEventArgs $event)
    {
        if ($setting->getName() !== Setting::BILLING || !$setting->getTeam()->isClientTeam() || !$event->hasChangedField('value')) {
            return;
        }

        $client = $setting->getTeam()->getClient();

        if (in_array($client->getStatus(), [Client::STATUS_BLOCKED_BILLING, Client::STATUS_BLOCKED_BILLING])
            && $setting->getValue() === false
        ) {
            $client->setStatus(Client::STATUS_CLIENT);
            $this->entityHistoryService->create($client, $client->getStatus(), EntityHistoryTypes::CLIENT_STATUS);

            $this->em->flush();
        }
    }

    public function preUpdate(Setting $setting, PreUpdateEventArgs $event)
    {
        $this->handleBilling($setting, $event);

        $setting->setUpdatedAt(new \DateTime());
    }
}