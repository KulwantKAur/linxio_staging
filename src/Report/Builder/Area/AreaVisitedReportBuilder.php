<?php

namespace App\Report\Builder\Area;

use App\Entity\AreaHistory;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;
use App\Service\User\UserServiceHelper;
use App\Service\Vehicle\VehicleService;
use App\Util\RequestFilterResolver\RequestFilterResolver;
use Doctrine\ORM\EntityManagerInterface;

class AreaVisitedReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_AREA_VISITED;

    public function getJson()
    {
        return self::generateData($this->params, $this->user, $this->vehicleService, $this->emSlave);
    }

    public function getPdf()
    {
        return AreaReportHelper::prepareExportData(
            self::generateData($this->params, $this->user, $this->vehicleService, $this->emSlave),
            $this->params, $this->user, $this->translator
        );
    }

    public function getCsv()
    {
        return AreaReportHelper::prepareExportData(
            self::generateData($this->params, $this->user, $this->vehicleService, $this->emSlave),
            $this->params, $this->user, $this->translator
        );
    }

    public static function generateData(
        array $params,
        User $user,
        VehicleService $vehicleService,
        EntityManagerInterface $em,
        $vehicleId = false
    ) {
        $resolvedParams = RequestFilterResolver::resolve($params);
        $params = $resolvedParams + $params;

        $vehicles = $vehicleService->vehicleList(
            self::getElasticSearchParamsForVisitedGeofences($params),
            $user,
            false
        );

        $params = UserServiceHelper::handleTeamParams($params, $user);

        $params['vehicles'] = array_map(function (Vehicle $vehicle) {
            return $vehicle->getId();
        }, $vehicles);

        return $em->getRepository(AreaHistory::class)->getVisitedAreas($params, $user, $vehicleId);
    }

    public static function getElasticSearchParamsForVisitedGeofences(array $params): array
    {
        return array_intersect_key(
            $params,
            array_flip(['defaultLabel', 'regNo', 'depot', 'groups', 'id', 'vehicleIds'])
        );
    }
}