<?php

namespace App\Report\Builder\Maintenance;

use App\Entity\ServiceRecord;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use App\Service\User\UserServiceHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

trait ServiceRecordDetailedTrait
{
    public function detailedReport(array $params, User $user, $type = null, $vehicleId = false)
    {
        $params = UserServiceHelper::handleTeamParams($params, $user);

        if ($user->needToCheckUserGroup()) {
            $vehicleIds = $this->em->getRepository(UserGroup::class)->getUserVehiclesIdFromUserGroup($user);
            if (isset($params['vehicleIds']) && count($params['vehicleIds'])) {
                $params['vehicleIds'] = array_intersect($vehicleIds, $params['vehicleIds']);
            } else {
                $params['vehicleIds'] = $vehicleIds;
            }
        }

        return $this->emSlave->getRepository(ServiceRecord::class)->getServiceDetailed(
            MaintenanceReportHelper::prepareFields($params),
            $type,
            $vehicleId
        );
    }

    public function detailedReportByVehicle(
        array $params,
        User $user,
        TranslatorInterface $translator,
        $type = ServiceRecord::TYPE_SERVICE_RECORD
    ) {
        $result = ['vehicles' => []];
        $result['total'] = [];
        unset($params['vehicleIds']);
        if ($type === ServiceRecord::TYPE_SERVICE_RECORD) {
            $vehicleIds = $this->serviceRecordService->getServiceRecordsVehicleIds($params, $user);
        } elseif ($type === ServiceRecord::TYPE_REPAIR) {
            $vehicleIds = $this->serviceRecordService->getRepairsVehicleIds($params, $user);
        } else {
            $vehicleIds = $this->serviceRecordService->getCommonVehicleIds($params, $user);
        }

        foreach (array_filter($vehicleIds) as $vehicleId) {
            /** @var Vehicle $vehicle */
            $vehicle = $this->em->getRepository(Vehicle::class)->find($vehicleId);
            if (!$vehicle) {
                continue;
            }
            $query = $this->detailedReport(array_merge($params, ['vehicleId' => $vehicleId]), $user, $type);

            $result['vehicles'][] = [
                'vehicle' => $vehicle->toArray(Vehicle::REPORT_VALUES),
                'data' => MaintenanceReportHelper::prepareExportData($query->execute()->fetchAll(),
                    $params, $user, $translator, ['sr_amount_total'])
            ];
        }

        return $result;
    }
}