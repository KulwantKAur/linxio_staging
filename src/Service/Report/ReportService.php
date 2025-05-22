<?php

namespace App\Service\Report;

use App\EntityManager\SlaveEntityManager;
use App\Report\Core\Exception\UndefinedReportException;
use App\Report\Core\Factory\ReportOutputFactory;
use App\Report\Core\Interfaces\ReportBuilderInterface;
use App\Report\Core\Factory\ReportBuilderFactory;
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
use Symfony\Contracts\Translation\TranslatorInterface;

class ReportService
{
    protected EntityManager $em;
    protected SlaveEntityManager $emSlave;
    protected TranslatorInterface $translator;
    protected ReportBuilderFactory $reportBuilder;
    protected ReportOutputFactory $outputFactory;
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

    public function __construct(
        EntityManager $em,
        SlaveEntityManager $emSlave,
        TranslatorInterface $translator,
        ReportBuilderFactory $reportBuilder,
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
        $this->reportBuilder = $reportBuilder;
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
     * @param string $reportName
     * @return ReportBuilderInterface
     * @throws UndefinedReportException
     */
    public function init(string $reportName): ReportBuilderInterface
    {
        $defaultParams = [
            $this->em,
            $this->emSlave,
            $this->translator,
            $this->outputFactory,
            $this->vehicleService,
            $this->settingService,
            $this->userService,
            $this->reminderService,
            $this->digitalFormAnswerService,
            $this->deviceSensorService,
            $this->paginator,
            $this->fuelCardReportService,
            $this->serviceRecordService,
            $this->billingService,
            $this->reportFacade,
            $this->eventLogReportService
        ];

        return $this->reportBuilder->getInstance($reportName, $defaultParams);
    }
}
