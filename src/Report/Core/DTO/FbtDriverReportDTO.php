<?php

namespace App\Report\Core\DTO;

use App\Enums\EntityFields;
use App\Service\BaseService;
use App\Util\StringHelper;
use Carbon\Carbon;

class FbtDriverReportDTO
{
    public string $startDate;
    public string $endDate;
    public string $order;
    public string $sort;
    public array $teamId;
    public array $driverIds;
    public array $vehicleIds;

    public function __construct(array $data)
    {
        $this->startDate = isset($data[EntityFields::START_DATE])
            ? BaseService::parseDateToUTC($data[EntityFields::START_DATE])
            : Carbon::now();
        $this->endDate = isset($data[EntityFields::END_DATE])
            ? BaseService::parseDateToUTC($data[EntityFields::END_DATE])
            : (clone $this->startDate)->addHours(24);
        $this->order = StringHelper::getOrder($data);
        $this->sort = StringHelper::getSort($data);
        $this->teamId = $data['teamId'] ?? [];
        $this->driverIds = $data['driverIds'] ?? $data['driverId'] ?? [];
        $this->vehicleIds = $data['vehicleIds'] ?? [];
    }
}