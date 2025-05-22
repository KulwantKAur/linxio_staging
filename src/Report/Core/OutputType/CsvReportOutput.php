<?php

namespace App\Report\Core\OutputType;

use App\Entity\User;
use App\Enums\FileExtension;
use App\Report\Core\Interfaces\ReportOutputInterface;
use App\Report\ReportBuilder;
use App\Response\CsvResponse;

class CsvReportOutput implements ReportOutputInterface
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return FileExtension::CSV;
    }

    /**
     * @param ReportBuilder $reportBuilder
     * @return CsvResponse
     */
    public function create(ReportBuilder $reportBuilder, User $user): CsvResponse
    {
        return new CsvResponse($reportBuilder->getCsv(), 200, [], true, [], $user);
    }
}
