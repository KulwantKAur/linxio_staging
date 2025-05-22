<?php

namespace App\Report\Core\DTO;

use App\Enums\EntityFields;
use App\Service\BaseService;
use App\Util\StringHelper;
use Carbon\Carbon;

class StopReportDTO
{
    public \DateTime $startDate;
    public \DateTime $endDate;
    public string $order;
    public string $sort;
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
    public $areaGroup = false;
    public $noAreaGroup;
    public $area;
    public $noArea;

    public function __construct(array $data)
    {
        $this->startDate = isset($data[EntityFields::START_DATE])
            ? BaseService::parseDateToUTC($data[EntityFields::START_DATE])
            : Carbon::now();
        $this->endDate = isset($data[EntityFields::END_DATE])
            ? BaseService::parseDateToUTC($data[EntityFields::END_DATE])
            : (clone $this->startDate)->addHours(24);

        $this->area = $data['areas_name'] ?? null;
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
        $this->order = StringHelper::getOrder($data);
        $this->sort = StringHelper::getSort($data, 'route_id');

        if (isset($data['groups']) && is_array($data['groups'])) {
            $this->vehicleGroup = implode(', ', array_diff($data['groups'], [null]));
            $this->noGroups = in_array(null, $data['groups'], true);
        }

        if (isset($data['depot']) && is_array($data['depot'])) {
            $this->depotId = implode(', ', array_diff($data['depot'], [null]));
            $this->noDepot = in_array(null, $data['depot'], true);
        }

        if (isset($data['areaGroup']) && is_array($data['areaGroup'])) {
            $this->areaGroup = array_diff($data['areaGroup'], [null]);
            $this->noAreaGroup = in_array(null, $data['areaGroup'], true);
        }

        if (isset($data['areas_name']) && is_array($data['areas_name'])) {
            $this->area = array_diff($data['areas_name'], [null]);
            $this->noArea = in_array(null, $data['areas_name'], true);
        }
    }
}