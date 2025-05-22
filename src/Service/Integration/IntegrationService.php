<?php

namespace App\Service\Integration;

use App\Entity\Integration;
use App\Entity\IntegrationData;
use App\Entity\IntegrationScope;
use App\Entity\Notification\Event;
use App\Entity\Team;
use App\Entity\Vehicle;
use App\Entity\VehicleGroup;
use App\Service\BaseService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use Doctrine\ORM\EntityManager;

class IntegrationService extends BaseService
{
    private $em;
    private NotificationEventDispatcher $notificationDispatcher;

    public function __construct(
        EntityManager $em,
        NotificationEventDispatcher $notificationDispatcher
    ) {
        $this->em = $em;
        $this->notificationDispatcher = $notificationDispatcher;
    }

    public function updateIntegrationData(array $data, $integrationId, Team $team): IntegrationData
    {
        $integration = $this->em->getRepository(Integration::class)->find($integrationId);
        $integrationData = $this->em->getRepository(IntegrationData::class)
            ->findOneBy(['team' => $team, 'integration' => $integration]);

        if (!$integrationData) {
            $integrationData = (new IntegrationData([]))->setTeam($team)->setIntegration($integration);
            $this->em->persist($integrationData);

            $scope = (new IntegrationScope([]))->setTeam($team)->setIntegration($integration);
            $this->em->persist($scope);

            $integrationData->setScope($scope);
        }

        $integrationData->setData($data);
        if ($integrationData && $integrationData->isRequireData()) {
            $integrationData->setStatus(IntegrationData::STATUS_ENABLED);
        }

        $this->em->flush();

        return $integrationData;
    }

    public function getScopeEntityIds(IntegrationScope $integrationScope)
    {
        return match ($integrationScope->getType()) {
            IntegrationScope::ANY_SCOPE => $this->em->getRepository(Vehicle::class)->getVehiclesByTeam($integrationScope->getTeam()),
            IntegrationScope::VEHICLE_SCOPE => $this->em->getRepository(Vehicle::class)
                ->getVehiclesByIdsAndTeam($integrationScope->getValue(), $integrationScope->getTeam()),
            IntegrationScope::DEPOT_SCOPE => $this->em->getRepository(Vehicle::class)
                ->getVehiclesByDepotIdsAndTeam($integrationScope->getValue(), $integrationScope->getTeam()),
            IntegrationScope::GROUP_SCOPE => $this->em->getRepository(VehicleGroup::class)
                ->getVehicleByGroupIdsAndTeam($integrationScope->getValue(), $integrationScope->getTeam()),
            default => []
        };
    }

    public function updateIntegrationStatus(?string $status, $integrationId, Team $team): IntegrationData
    {
        $integration = $this->em->getRepository(Integration::class)->find($integrationId);
        $integrationData = $this->em->getRepository(IntegrationData::class)
            ->findOneBy(['team' => $team, 'integration' => $integration]);

        if (!$integrationData) {
            $integrationData = $this->updateIntegrationData([], $integrationId, $team);
        }

        if (!$status) {
            return $integrationData;
        }

        if ($status === IntegrationData::STATUS_DISABLED) {
            $integrationData->setStatus(IntegrationData::STATUS_DISABLED);
        } elseif ($status === IntegrationData::STATUS_ENABLED
            && ($integration->getName() !== Integration::FLEETIO || $integrationData->getData())) {
            if ($integrationData->getStatus() === IntegrationData::STATUS_DISABLED) {
                $integrationEnabled = true;
            }
            $integrationData->setStatus(IntegrationData::STATUS_ENABLED);
        } else {
            $integrationData->setStatus(IntegrationData::STATUS_REQUIRE_DATA);
        }

        $this->em->flush();

        if ($integrationEnabled ?? null) {
            $this->notificationDispatcher->dispatch(Event::INTEGRATION_ENABLED,
                $integrationData->getTeam()->getClient(), null, ['integrationName' => $integration->getName()]);
        }

        return $integrationData;
    }

    public function updateIntegrationScope($scope, $integrationId, Team $team): IntegrationData
    {
        $integration = $this->em->getRepository(Integration::class)->find($integrationId);
        $integrationData = $this->em->getRepository(IntegrationData::class)
            ->findOneBy(['team' => $team, 'integration' => $integration]);

        if (!$integrationData) {
            $integrationData = $this->updateIntegrationData([], $integrationId, $team);
        }
        $integrationData->getScope()->setType($scope['type'] ?? IntegrationScope::STATUS_ALL);
        $integrationData->getScope()->setValue($scope['value'] ?? null);

        $this->em->flush();

        return $integrationData;
    }
}