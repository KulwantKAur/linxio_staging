<?php

namespace App\Service\Device;

use App\Entity\Team;
use App\Entity\User;
use App\Service\Device\Services\AdminDeviceService;
use App\Service\Device\Services\ClientDeviceService;
use App\Service\Device\Services\DeviceServiceAbstract;
use App\Service\Device\Services\ResellerDeviceService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DeviceServiceResolver
{
    private $container;

    /**
     * TrackerFactory constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     *
     * @param User $currentUser
     * @return DeviceServiceAbstract
     * @throws \Exception
     */
    public function getInstance(User $currentUser): DeviceServiceAbstract
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        switch ($currentUser->getTeam()->getType()) {
            case Team::TEAM_ADMIN:
                $deviceService = new AdminDeviceService($em, $currentUser);
                break;
            case Team::TEAM_CLIENT:
                $deviceService = new ClientDeviceService($em, $currentUser);
                break;
            case Team::TEAM_RESELLER:
                $deviceService = new ResellerDeviceService($em, $currentUser);
                break;
            default:
                throw new \Exception('Unsupported team type: ' . $currentUser->getTeam()->getType());
        }

        return $deviceService;
    }
}