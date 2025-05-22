<?php

namespace App\Response;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

class XlsxResponse extends Response
{
    private $serializer;
    private $data;
    private $context = [];
    private $response;
    private $responseData;
    private $writer;


    public function __construct(
        array $data = [],
        $status = 200,
        $headers = [],
        $noCsvHeader = true,
        $defaultContext = []
    ) {
        parent::__construct('', $status, $headers);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data);

        $this->writer = new Xlsx($spreadsheet);

//        $writer->save('php://output');
//        $this->responseData = ob_get_contents();
//        ob_end_clean();
//
//        $this->response = new StreamedResponse(
//            function () use ($writer) {
//                $writer->save('php://output');
//            }
//        );
//        $this->response->headers->set('Content-Type', 'application/vnd.ms-excel');
//        $this->response->headers->set('Content-Disposition', 'attachment;filename="'.sprintf('file_%s.xlsx', time()).'"');
//        $this->response->headers->set('Cache-Control', 'max-age=0');
    }

    public function getData()
    {
        $writer = $this->writer;

        ob_start();
        $writer->save('php://output');
        $data = ob_get_contents();
        ob_end_clean();

        return $data;
    }
}