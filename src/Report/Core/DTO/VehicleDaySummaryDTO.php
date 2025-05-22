<?php

namespace App\Report\Core\DTO;

class VehicleDaySummaryDTO
{
    public string $startDate;
    public string $endDate;
    public string $order;
    public string $sort;
    public array $vehicles;

    public function __construct(array $data)
    {
        $this->startDate = $data['startDate'];
        $this->endDate = $data['endDate'];
        $this->vehicles = $data['vehicles'] ?? [];
        $this->order = $data['order'] ?? 'ASC';
        $this->sort = $data['sort'] ?? 'id';
        if ($this->sort === 'date') {
            $this->sort = 'id';
        }
    }
}