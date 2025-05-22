<?php

namespace App\Report\Core\OutputType;

use App\Entity\User;
use App\Enums\FileExtension;
use App\Report\Core\Interfaces\ReportOutputInterface;
use App\Report\ReportBuilder;
use App\Response\PdfResponse;
use App\Service\PdfService;

class PdfReportOutput implements ReportOutputInterface
{
    protected PdfService $pdfService;

    /**
     * PdfReportOutput constructor.
     * @param PdfService $pdfService
     */
    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return FileExtension::PDF;
    }

    /**
     * @param ReportBuilder $reportBuilder
     * @return PdfResponse
     */
    public function create(ReportBuilder $reportBuilder, User $user): PdfResponse
    {
        $pdf = $this->pdfService->getReportPdf(
            $reportBuilder->getPdf() ?? [],
            $reportBuilder::REPORT_TYPE,
            $reportBuilder::REPORT_TEMPLATE,
            $user
        );

        return new PdfResponse($pdf);
    }
}
