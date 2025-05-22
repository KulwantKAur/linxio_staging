<?php

namespace App\Report\Builder\Route\Traits;

use App\Entity\Route;
use App\Entity\UserGroup;
use App\Report\Core\DTO\StopReportDTO;
use App\Service\User\UserServiceHelper;
use App\Service\Vehicle\VehicleServiceHelper;

trait StopTrait
{
    public function generateData()
    {
        $params = UserServiceHelper::handleTeamParams($this->params, $this->user);

        if ($this->user->needToCheckUserGroup()) {
            $vehicleIds = $this->em->getRepository(UserGroup::class)->getUserVehiclesIdFromUserGroup($this->user);
            $params['vehicleIds'] = $vehicleIds;
        }

        $params = VehicleServiceHelper::handleDriverVehicleParams($params, $this->em, $this->user);

        return $this->emSlave->getRepository(Route::class)->getStopsSummary(new StopReportDTO($params));
    }
}