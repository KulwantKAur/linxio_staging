<?php

namespace App\Service\Device\Services;

use App\Entity\Device;
use App\Entity\Reseller;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

class ResellerDeviceService extends DeviceServiceAbstract
{
    private $em;
    protected $currentUser;

    public function __construct(EntityManager $em, User $currentUser)
    {
        $this->em = $em;
        $this->currentUser = $currentUser;
    }

    public function getById(int $id): ?Device
    {
        return $this->em->getRepository(Device::class)
            ->getDeviceByIdAndCreatedByTeam($id, $this->currentUser->getTeam());
    }

    public function prepareDeviceListParams(array $params): array
    {
        $resellerClientTeamIds = $this->em->getRepository(Reseller::class)->getResellerClientTeams($this->currentUser->getReseller());
        $resellerClientTeamIds[] = $this->currentUser->getTeam()->getId();
        if (isset($params['teamId'])) {
            $team = $this->em->getRepository(Team::class)->find($params['teamId']);
            if (!$team || !$this->currentUser->getReseller()->checkAsResellerTeamAccess($team)) {
                $params['teamId'] = $resellerClientTeamIds;
            }
        } else {
            $params['teamId'] = $resellerClientTeamIds;
        }

        $params = $this->handleDeviceModelAndVendor($params);

        return $params;
    }
}