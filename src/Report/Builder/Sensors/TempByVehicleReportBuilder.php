<?php

namespace App\Report\Builder\Sensors;

use App\Entity\DeviceSensor;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Report\ReportBuilder;
use App\Service\BaseService;
use App\Service\Report\ReportMapper;
use App\Service\User\UserServiceHelper;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;

class TempByVehicleReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_TEMPERATURE_BY_VEHICLE;
    public const REPORT_TEMPLATE = 'reports/report-by-vehicle.html.twig';

    public function getJson()
    {
        $pagination = $this->paginator->paginate(
            self::generateDate($this->params, $this->user, $this->emSlave),
            $this->page, ($this->limit == 0) ? 1 : $this->limit
        );

        if ($this->limit == 0 && $pagination->getTotalItemCount() > 0) {
            $pagination = $this->paginator->paginate(
                self::generateDate($this->params, $this->user, $this->emSlave), 1, $pagination->getTotalItemCount()
            );
        }

        $pagination->setItems(
            BaseService::replaceNestedArrayKeysToCamelCase($pagination->getItems())
        );

        return $pagination;
    }

    public function getPdf()
    {
        $data = $this->deviceSensorService->getDeviceTempAndHumiditySensorReportQueryByVehiclePdf($this->params, $this->user);
        return $data;
    }

    public function getCsv()
    {
        return $this->deviceSensorService->prepareExportData(
            self::generateDate($this->params, $this->user, $this->emSlave), $this->params, $this->user
        );
    }

    public static function generateDate(array $params, User $user, EntityManagerInterface $em): QueryBuilder
    {
        $params = UserServiceHelper::handleTeamParams($params, $user);
        if ($user->needToCheckUserGroup()) {
            $vehicleIds = $em->getRepository(UserGroup::class)->getUserVehiclesIdFromUserGroup($user);
            $params['vehicleIds'] = $vehicleIds;
        }

        return $em->getRepository(DeviceSensor::class)->getDeviceTempAndHumiditySensorsSummaryByVehicle(
            SensorReportHelper::prepareFieldsForReportByVehicle($params),
            false,
            $params['sensorsList'] ?? false,
            $params['chart'] ?? false
        );
    }
}