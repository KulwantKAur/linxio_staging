<?php

namespace App\Report\Core\DTO;

use App\Enums\EntityFields;
use App\Enums\SqlEntityFields;
use App\Service\BaseService;
use App\Util\StringHelper;
use Carbon\Carbon;

class RouteReportDTO
{
    public \DateTime $startDate;
    public \DateTime $endDate;
    public string $order;
    public string $sort;
    public $areaFrom;
    public $noAreaFrom = null;
    public $areaTo;
    public $noAreaTo = null;
    public $defaultLabel;
    public $vehicleRegNo;
    public $driver;
    public $driverId;
    public $vehicleDepot;
    public $vehicleId;
    public $vehicleIds;
    public $teamId;
    public $vehicleGroup;
    public $noGroups;
    public $noDepot;
    public $depotId;
    public $reportRoutesCorrectedOnly;
    public $areaGroupStart = false;
    public $noAreaGroupStart;
    public $areaGroupFinish = false;
    public $noAreaGroupFinish;
    public $tripCode;

    public function __construct(array $data)
    {
        $this->startDate = isset($data[EntityFields::START_DATE])
            ? BaseService::parseDateToUTC($data[EntityFields::START_DATE])
            : Carbon::now();
        $this->endDate = isset($data[EntityFields::END_DATE])
            ? BaseService::parseDateToUTC($data[EntityFields::END_DATE])
            : (clone $this->startDate)->addHours(24);

        $this->areaFrom = $data['start_areas_name'] ?? null;
        $this->areaTo = $data['finish_areas_name'] ?? null;
        $this->defaultLabel = $data['defaultLabel'] ?? null;
        $this->vehicleRegNo = $data['vehicleRegNo'] ?? null;
        $this->driver = $data['driver'] ?? null;
        $this->driverId = $data['driverId'] ?? null;
        $this->vehicleGroup = $data['vehicleGroup'] ?? null;
        $this->vehicleDepot = $data['vehicleDepot'] ?? null;
        $this->depotId = $data['depotId'] ?? null;
        $this->noDepot = $data['noDepot'] ?? false;
        $this->noGroups = $data['noGroups'] ?? false;
        $this->vehicleId = $data['vehicleId'] ?? null;
        $this->vehicleIds = empty($data['vehicleIds']) ? null : $data['vehicleIds'];
        $this->teamId = $data['teamId'] ?? null;
        $this->reportRoutesCorrectedOnly = StringHelper::stringToBool($data['reportRoutesCorrectedOnly'] ?? false);
        $this->order = StringHelper::getOrder($data);
        $this->sort = StringHelper::getSort($data, 'route_id');
        $this->tripCode = $data['trip_code'] ?? null;

        if (isset($data['groups']) && is_array($data['groups'])) {
            $this->vehicleGroup = implode(', ', array_diff($data['groups'], [null]));
            $this->noGroups = in_array(null, $data['groups'], true);
        }

        if (isset($data['depot']) && is_array($data['depot'])) {
            $this->depotId = implode(', ', array_diff($data['depot'], [null]));
            $this->noDepot = in_array(null, $data['depot'], true);
        }

        if (isset($data['areaGroupStart']) && is_array($data['areaGroupStart'])) {
            $this->areaGroupStart = array_diff($data['areaGroupStart'], [null]);
            $this->noAreaGroupStart = in_array(null, $data['areaGroupStart'], true);
        }

        if (isset($data['areaGroupFinish']) && is_array($data['areaGroupFinish'])) {
            $this->areaGroupFinish = array_diff($data['areaGroupFinish'], [null]);
            $this->noAreaGroupFinish = in_array(null, $data['areaGroupFinish'], true);
        }

        if (isset($data['start_areas_name']) && is_array($data['start_areas_name'])) {
            $this->areaFrom = array_diff($data['start_areas_name'], [null]);
            $this->noAreaFrom = in_array(null, $data['start_areas_name'], true);
        }

        if (isset($data['finish_areas_name']) && is_array($data['finish_areas_name'])) {
            $this->areaTo = array_diff($data['finish_areas_name'], [null]);
            $this->noAreaTo = in_array(null, $data['finish_areas_name'], true);
        }
    }
}