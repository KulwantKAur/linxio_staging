<?php

namespace App\Report\Core\Factory;

use App\Enums\FileExtension;
use App\Report\Core\Exception\UndefinedReportOutputException;
use App\Report\Core\Interfaces\ReportOutputInterface;
use App\Report\Core\OutputType\CsvReportOutput;
use App\Report\Core\OutputType\JsonReportOutput;
use App\Report\Core\OutputType\PdfReportOutput;
use App\Report\Core\OutputType\XlsxReportOutput;
use App\Service\PdfService;
use Knp\Component\Pager\PaginatorInterface;

class ReportOutputFactory
{
    private PaginatorInterface $paginator;
    private PdfService $pdfService;

    public function __construct(PaginatorInterface $paginator, PdfService $pdfService)
    {
        $this->paginator = $paginator;
        $this->pdfService = $pdfService;
    }

    public function getInstance(string $outputType): ReportOutputInterface
    {
        return match ($outputType) {
            FileExtension::JSON => new JsonReportOutput($this->paginator),
            FileExtension::CSV => new CsvReportOutput(),
            FileExtension::XLSX => new XlsxReportOutput(),
            FileExtension::PDF => new PdfReportOutput($this->pdfService),
            default => throw new UndefinedReportOutputException(
                sprintf(
                    'Unsupported output type "%s".',
                    $outputType
                )
            ),
        };
    }

}