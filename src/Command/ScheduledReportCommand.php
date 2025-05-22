<?php

namespace App\Command;

use App\Command\Traits\CommandLoggerTrait;
use App\Command\Traits\DBTimeoutTrait;
use App\Entity\ScheduledReport;
use App\Mailer\MailSender;
use App\Mailer\Render\TwigEmailRender;
use App\Service\ScheduledReport\ScheduledReportService;
use App\Util\StringHelper;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:scheduled-report')]
class ScheduledReportCommand extends Command
{
    use CommandLoggerTrait;
    use DBTimeoutTrait;

    private $em;
    private $scheduledReportService;
    private $mailSender;
    private TwigEmailRender $emailRender;

    protected function configure(): void
    {
        $this->setDescription('Scheduled report');
        $this->updateConfigWithDBTimeoutOptions();
    }

    public function __construct(
        EntityManager $em,
        ScheduledReportService $scheduledReportService,
        MailSender $mailSender,
        TwigEmailRender $emailRender,
        private readonly Logger $logger
    ) {
        $this->em = $em;
        $this->scheduledReportService = $scheduledReportService;
        $this->mailSender = $mailSender;
        $this->emailRender = $emailRender;

        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $scheduledReports = $this->em->getRepository(ScheduledReport::class)->findBy(['status' => ScheduledReport::STATUS_ACTIVE]);

        /** @var ScheduledReport $scheduledReport */
        foreach ($scheduledReports as $scheduledReport) {
            $startTs = time();
            $timezoneName = $scheduledReport->getTimezoneName();

            try {
                $this->disableDBTimeoutByInput($input);
                $nextSendDate = $scheduledReport->getCronExpression()
                    ->getNextRunDate($scheduledReport->getSentAt(), 0, false, $timezoneName);
                while ($nextSendDate < (new \DateTime())) {
                    $attachment = $this->getReportAttachment($scheduledReport, clone $nextSendDate);
                    $data = $scheduledReport->getStartAndEndDate($nextSendDate);
                    $message = $this->getRenderedEmail($scheduledReport, $data['startDate'], $data['endDate']);
                    $emails = $this->scheduledReportService->getRecipientsEmails($scheduledReport);
//                    $output->writeln('id: ' . $scheduledReport->getId());
//                    $output->writeln(json_encode($emails));
                    if (!empty($emails)) {
                        $this->mailSender->sendEmail($emails, $message,
                            $scheduledReport->getTeam()->getNotificationEmail(), [], null, [$attachment]);
                    }

                    $scheduledReport->setSentAt((clone $nextSendDate)->setTimezone(new \DateTimeZone('utc')));
                    $nextSendDate = $scheduledReport->getCronExpression()
                        ->getNextRunDate($nextSendDate, 0, false, $timezoneName);
                }

                $this->em->flush();

                if (time() - $startTs > 30) {
                    $output->writeln('Report time > 30s: ' . time() - $startTs . ' sec, id: ' . $scheduledReport->getId());
                }

                $this->enableDBTimeoutByInput($input);
            } catch (\Throwable $exception) {
                $output->writeln('Exception report id:' . $scheduledReport->getId());
//            $output->writeln(PHP_EOL . $exception->getMessage());
//            $output->writeln(PHP_EOL . $exception->getTraceAsString());
                $this->logException($exception);
            }
        }

        return 0;
    }

    private function getReportAttachment(ScheduledReport $scheduledReport, $nextSendDate)
    {
        $attachment = $this->scheduledReportService->getReportData($scheduledReport, $nextSendDate);

        return MailSender::getScheduledReportEmailAttachment(
            $scheduledReport,
            $attachment->getData(),
            $this->getFileName($scheduledReport, $nextSendDate)
        );
    }

    public function getRenderedEmail(ScheduledReport $scheduledReport, $startDate, $endDate)
    {
        if ($scheduledReport->isIntervalDaily() && $scheduledReport->getIntervalObject()->totalDays == 1) {
            $dateRange = 'Date range: ' . $startDate->setTimeZone($scheduledReport->getTimezone())->format('Y-m-d');
        } else {
            $dateRange = 'Date range: ' . $startDate->setTimeZone($scheduledReport->getTimezone())->format('Y-m-d')
                . ' - ' . $endDate->setTimeZone($scheduledReport->getTimezone())->format('Y-m-d') . '</br>';
        }

        $company = $scheduledReport->getTeam()->getClient()?->getReseller()?->getCompanyName() ?? 'Linxio';

        return $this->emailRender->render(
            'emails/scheduledReport.html.twig',
            [
                'subject' => $company . ' report - ' . $scheduledReport->getName() . ' ' . (new Carbon())->setTimeZone($scheduledReport->getTimezone())
                        ->format($scheduledReport->getDateFormat()),
                'body' => 'Please find attached the scheduled report - ' . $scheduledReport->getName() . '<br/><br/>' .
                    'Type: ' . ucfirst($scheduledReport->getType()) . '<br/>' .
                    $dateRange . '<br/><br/>',
                'emailName' => $scheduledReport->getTeam()->getEmailName(),
                'logoPath' => $scheduledReport->getTeam()->getLogoPath()
            ]
        );
    }

    public function getFileName(ScheduledReport $scheduledReport, \DateTime $date)
    {
        $filename = 'linxio ' . $scheduledReport->getName() . ' '
            . $date->setTimezone($scheduledReport->getTimezone())->format('Y-m-d') . '.' . $scheduledReport->getFormat();

        return StringHelper::removeFileNameSpecialChars($filename);
    }
}