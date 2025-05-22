<?php

namespace App\Report\Core\Interfaces;

use App\Entity\User;
use App\Report\ReportBuilder;

interface ReportOutputInterface
{
    public function getType(): string;

    public function create(ReportBuilder $data, User $user);
}
