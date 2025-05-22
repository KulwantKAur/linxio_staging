<?php

namespace App\Report\Core\ResponseType;

interface IReportResponse
{
    public function getData();
    public function getFirstItem();
}