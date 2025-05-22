<?php

namespace App\Report\Core\Interfaces;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

interface ReportBuilderInterface
{
    public function getReport(string $type, array $params, User $user): Response;
}
