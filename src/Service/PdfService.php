<?php

namespace App\Service;

use App\Entity\AdminTeamInfo;
use App\Entity\Invoice;
use App\Entity\PlatformSetting;
use App\Entity\DigitalFormAnswer;
use App\Entity\User;
use Carbon\Carbon;
use mikehaertl\wkhtmlto\Pdf;
use Twig\Environment;

class PdfService extends BaseService
{
    private $templating;

    public function __construct(Environment $templating)
    {
        $this->templating = $templating;
    }

    public static function generatePdf($html = '', $orientation = 'Landscape')
    {
        $pdf = new Pdf($html);
        $pdf->setOptions(['orientation' => $orientation, 'encoding' => 'UTF-8']);

        return $pdf->toString();
    }

    public function getReportPdf(array $data, string $name, $template = 'reports/report.html.twig', ?User $user = null)
    {
        $contents = $this->templating->render($template, [
            'data' => $data,
            'reportName' => $name,
            'logoPath' => $user->getTeam()?->getLogoPath(),
            'timezoneText' => $user->getTimezoneText()

        ]);

        return PdfService::generatePdf($contents);
    }

    public function getDigitalFormAnswerPdf(
        DigitalFormAnswer $digitalFormAnswer,
        ?string $name = null,
        $template = 'digitalForm/answer.html.twig'
    ) {
        $contents = $this->templating->render($template,
            [
                'answer' => $digitalFormAnswer,
                'name' => $name,
            ]);

        return PdfService::generatePdf($contents, 'Portrait');
    }

    public function getInvoicePdf(
        Invoice $invoice,
        AdminTeamInfo $adminTeamInfo,
        ?PlatformSetting $platformSetting,
        ?string $name = null,
        $template = 'invoice/invoice.html.twig'
    ): bool|string {
        $contents = $this->templating->render($template,
            [
                'invoice' => $invoice,
                'adminTeamInfo' => $adminTeamInfo,
                'name' => $name,
                'platformSetting' => $platformSetting
            ]);

        return PdfService::generatePdf($contents, 'Portrait');
    }
}