<?php

namespace App\Report;

use App\Entity\User;
use App\EntityManager\SlaveEntityManager;
use App\Report\Core\Factory\ReportOutputFactory;
use App\Report\Core\Interfaces\ReportBuilderInterface;
use App\Report\Core\Interfaces\ReportOutputInterface;
use App\Service\Billing\BillingService;
use App\Service\Device\DeviceSensorService;
use App\Service\DigitalForm\DigitalFormAnswerService;
use App\Service\EventLog\Interfaces\ReportFacadeInterface;
use App\Service\EventLog\Report\EventLogReportService;
use App\Service\FuelCard\Report\FuelCardReportService;
use App\Service\Reminder\ReminderService;
use App\Service\ServiceRecord\ServiceRecordService;
use App\Service\Setting\SettingService;
use App\Service\User\UserService;
use App\Service\Vehicle\VehicleService;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class ReportBuilder implements ReportBuilderInterface
{
    protected EntityManager $em;
    protected SlaveEntityManager $emSlave;
    protected TranslatorInterface $translator;
    protected ReportOutputFactory $outputFactory;
    protected ReportOutputInterface $output;
    protected VehicleService $vehicleService;
    protected SettingService $settingService;
    protected UserService $userService;
    protected ReminderService $reminderService;
    protected DigitalFormAnswerService $digitalFormAnswerService;
    protected DeviceSensorService $deviceSensorService;
    protected PaginatorInterface $paginator;
    protected FuelCardReportService $fuelCardReportService;
    protected ServiceRecordService $serviceRecordService;
    protected BillingService $billingService;
    protected ReportFacadeInterface $reportFacade;
    protected EventLogReportService $eventLogReportService;
    protected ?User $user = null;
    protected array $params = [];
    public int $page = 1;
    public int $limit = 10;

    public const REPORT_TYPE = null;
    public const REPORT_TEMPLATE = 'reports/report.html.twig';

    public function __construct(
        EntityManager $em,
        SlaveEntityManager $emSlave,
        TranslatorInterface $translator,
        ReportOutputFactory $outputFactory,
        VehicleService $vehicleService,
        SettingService $settingService,
        UserService $userService,
        ReminderService $reminderService,
        DigitalFormAnswerService $digitalFormAnswerService,
        DeviceSensorService $deviceSensorService,
        PaginatorInterface $paginator,
        FuelCardReportService $fuelCardReportService,
        ServiceRecordService $serviceRecordService,
        BillingService $billingService,
        ReportFacadeInterface $reportFacade,
        EventLogReportService $eventLogReportService
    ) {
        $this->em = $em;
        $this->emSlave = $emSlave;
        $this->translator = $translator;
        $this->outputFactory = $outputFactory;
        $this->vehicleService = $vehicleService;
        $this->settingService = $settingService;
        $this->userService = $userService;
        $this->reminderService = $reminderService;
        $this->digitalFormAnswerService = $digitalFormAnswerService;
        $this->deviceSensorService = $deviceSensorService;
        $this->paginator = $paginator;
        $this->fuelCardReportService = $fuelCardReportService;
        $this->serviceRecordService = $serviceRecordService;
        $this->billingService = $billingService;
        $this->reportFacade = $reportFacade;
        $this->eventLogReportService = $eventLogReportService;
    }

    /**
     * @param string $outputType
     * @param array $params
     * @param User $user
     * @return Response
     * @throws Core\Exception\UndefinedReportOutputException
     */
    public function getReport(string $outputType, array $params, User $user): Response
    {
        $this->user = $user;
        $this->params = $params;
        $this->page = $params['page'] ?? 1;
        $this->limit = $params['limit'] ?? 10;
        $this->output = $this->outputFactory->getInstance($outputType);

        return $this->output->create($this, $this->user);
    }

    abstract public function getJson();

    abstract public function getPdf();

    abstract public function getCsv();

    public function getXlsx()
    {
    }
}
