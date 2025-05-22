<?php

namespace App\Report\Builder\Maintenance;

use App\Entity\ServiceRecord;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Report\ReportBuilder;
use App\Service\Report\ReportMapper;
use App\Service\User\UserServiceHelper;
use Doctrine\ORM\EntityManagerInterface;

class ServiceRecordsSummaryReportBuilder extends ReportBuilder
{
    public const REPORT_TYPE = ReportMapper::TYPE_SERVICE_RECORDS_SUMMARY;

    public function getJson()
    {
        return self::generateData($this->params, $this->user, $this->emSlave, false);
    }

    public function getPdf()
    {
        return MaintenanceReportHelper::prepareExportData(
            self::generateData($this->params, $this->user, $this->emSlave, false)->execute()->fetchAll(),
            $this->params,
            $this->user,
            $this->translator
        );
    }

    public function getCsv()
    {
        return MaintenanceReportHelper::prepareExportData(
            self::generateData($this->params, $this->user, $this->emSlave, false)->execute()->fetchAll(),
            $this->params,
            $this->user,
            $this->translator
        );
    }

    public static function generateData(array $params, User $user, EntityManagerInterface $em, $vehicleId = false)
    {
        $params = UserServiceHelper::handleTeamParams($params, $user);

        if ($user->needToCheckUserGroup()) {
            $vehicleIds = $em->getRepository(UserGroup::class)->getUserVehiclesIdFromUserGroup($user);
            if (isset($params['vehicleIds']) && count($params['vehicleIds'])) {
                $params['vehicleIds'] = array_intersect($vehicleIds, $params['vehicleIds']);
            } else {
                $params['vehicleIds'] = $vehicleIds;
            }
        }

        return $em->getRepository(ServiceRecord::class)
            ->getServiceSummary(MaintenanceReportHelper::prepareFields($params), $vehicleId);
    }
}