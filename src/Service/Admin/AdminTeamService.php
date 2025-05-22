<?php

namespace App\Service\Admin;

use App\Entity\AdminTeamInfo;
use App\Entity\Team;
use App\Service\BaseService;
use Doctrine\ORM\EntityManager;

class AdminTeamService extends BaseService
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function createAdminTeamInfo(array $data, Team $team): AdminTeamInfo
    {
        $adminTeamInfo = new AdminTeamInfo($data);
        $adminTeamInfo->setTeam($team);

        $this->em->persist($adminTeamInfo);
        $this->em->flush();

        return $adminTeamInfo;
    }
}