<?php

namespace App\Report\Core\ResponseType;

class ArrayResponse implements IReportResponse
{
    private array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getFirstItem()
    {
        return $this->data[0] ?? null;
    }
}