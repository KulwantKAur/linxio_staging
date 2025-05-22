<?php

namespace App\Report\Builder\Route;

use App\Entity\BaseEntity;
use App\Entity\Route;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Report\Builder\Route\Traits\StopTrait;
use App\Report\Core\DTO\StopReportDTO;
use App\Service\Report\ReportMapper;
use App\Service\User\UserServiceHelper;
use App\Service\Vehicle\VehicleServiceHelper;
use Doctrine\ORM\EntityManagerInterface;

class StopByVehicleReportBuilder extends StopReportBuilder
{
    use StopTrait;

    public const REPORT_TYPE = ReportMapper::TYPE_STOPS_BY_VEHICLE;
    public const REPORT_TEMPLATE = 'reports/stop-by-vehicle.html.twig';

    public function getJson()
    {
        return $this->generateData();
    }

    public function getCsv()
    {
        $this->params['fields'][] = 'regno'; //customer ask to add it for csv

        return RouteReportHelper::prepareExportData(
            $this->generateData(), $this->params, $this->user, $this->translator
        );
    }

    public function getPdf()
    {
        return $this->generatePdfData();
    }

    public function generatePdfData()
    {
        $vehicleData = $this->getStopsVehiclesData($this->params, $this->user, $this->emSlave);
        $vehicleIds = array_column($vehicleData, 'id');
        $result = ['vehicles' => []];
        $result['total'] = RouteReportHelper::prepareStopRouteReportTotalData($vehicleData, true);
        unset($this->params['vehicleIds']);

        foreach (array_filter($vehicleIds) as $vehicleId) {
            /** @var Vehicle $vehicle */
            $vehicle = $this->em->getRepository(Vehicle::class)->find($vehicleId);
            if (!$vehicle) {
                continue;
            }
            $this->params = array_merge($this->params, ['vehicleId' => $vehicleId]);

            $result['vehicles'][] = [
                'vehicle' => $vehicle->toArray(Vehicle::REPORT_VALUES),
                'data' => RouteReportHelper::prepareExportData(
                    $this->generateData(), $this->params, $this->user, $this->translator, ['parking_time_total']
                )
            ];
        }

        return $result;
    }

    public static function getStopsVehiclesData(array $params, User $user, EntityManagerInterface $em)
    {
        $params = UserServiceHelper::handleTeamParams($params, $user);
        $params['status'] = BaseEntity::STATUS_ALL;
        $params = VehicleServiceHelper::handleDriverVehicleParams($params, $em, $user);

        return $em->getRepository(Route::class)
            ->getStopsSummary(new StopReportDTO($params), true)->execute()->fetchAll();
    }
}