<?php

namespace App\Report\Core\OutputType;

use App\Entity\User;
use App\Enums\FileExtension;
use App\Report\Core\Interfaces\ReportOutputInterface;
use App\Report\ReportBuilder;
use App\Response\XlsxResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class XlsxReportOutput implements ReportOutputInterface
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return FileExtension::XLSX;
    }

    public function create(ReportBuilder $reportBuilder, User $user)
    {
        $xlsxResponse = new XlsxResponse($reportBuilder->getCsv());
//        $spreadsheet = new Spreadsheet();
//        $sheet = $spreadsheet->getActiveSheet();
//        $sheet->fromArray($reportBuilder->getCsv());
//
//        $writer = new Xlsx($spreadsheet);
//
//        $response = new StreamedResponse(
//            function () use ($writer) {
//                $writer->save('php://output');
//            }
//        );
//        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
//        $response->headers->set('Content-Disposition', 'attachment;filename="'.sprintf('file_%s.xlsx', time()).'"');
//        $response->headers->set('Cache-Control', 'max-age=0');

        return $xlsxResponse;
    }
}
