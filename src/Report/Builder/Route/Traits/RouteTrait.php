<?php

namespace App\Report\Builder\Route\Traits;

use App\Entity\Route;
use App\Entity\UserGroup;
use App\Report\Core\DTO\RouteReportDTO;
use App\Service\User\UserServiceHelper;
use App\Service\Vehicle\VehicleServiceHelper;

trait RouteTrait
{
    /**
     * @return mixed
     */
    public function generateData()
    {
        $this->params = UserServiceHelper::handleTeamParams($this->params, $this->user);

        if ($this->user->needToCheckUserGroup()) {
            $vehicleIds = $this->em->getRepository(UserGroup::class)->getUserVehiclesIdFromUserGroup($this->user);
            $this->params['vehicleIds'] = $vehicleIds;
        }

        $this->params = VehicleServiceHelper::handleDriverVehicleParams($this->params, $this->em, $this->user);

        return $this->emSlave->getRepository(Route::class)->getRoutesSummary(new RouteReportDTO($this->params));
    }
}