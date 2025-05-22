<?php

namespace App\Report\Core\DataType;

class DataWithTotal
{
    public $data;
    public $total;

    public function __construct($data, $total)
    {
        $this->data = $data;
        $this->total = $total;
    }
}