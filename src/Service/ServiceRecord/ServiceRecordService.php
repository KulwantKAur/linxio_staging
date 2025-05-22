<?php

namespace App\Service\ServiceRecord;

use App\Entity\Asset;
use App\Entity\BaseEntity;
use App\Entity\File;
use App\Entity\Notification\Event;
use App\Entity\Reminder;
use App\Entity\ServiceRecord;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use App\EntityManager\SlaveEntityManager;
use App\Enums\SqlEntityFields;
use App\Events\Repair\RepairCreatedEvent;
use App\Events\Repair\RepairDeletedEvent;
use App\Events\Repair\RepairUpdatedEvent;
use App\Events\ServiceRecord\ServiceRecordCreatedEvent;
use App\Events\ServiceRecord\ServiceRecordDeletedEvent;
use App\Events\ServiceRecord\ServiceRecordUpdatedEvent;
use App\Report\Builder\Maintenance\MaintenanceReportHelper;
use App\Service\BaseService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\File\FileService;
use App\Service\Redis\RedisService;
use App\Service\User\UserServiceHelper;
use App\Service\Validation\ValidationService;
use App\Service\Vehicle\VehicleService;
use App\Util\ArrayHelper;
use App\Util\DateHelper;
use App\Util\StringHelper;
use Carbon\Carbon;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\Notification\EventDispatcher;

class ServiceRecordService extends BaseService
{
    use ServiceRecordFieldsTrait;

    protected $translator;
    private $em;
    private $serviceRecordFinder;
    private $eventDispatcher;
    private $validationService;
    private $fileService;
    private $notificationDispatcher;
    private $vehicleService;
    protected $dashboardCache;
    protected $emSlave;

    public const ELASTIC_NESTED_FIELDS = [];
    public const ELASTIC_SIMPLE_FIELDS = [
        'id' => 'id',
        'reminderId' => 'reminderId',
        'status' => 'status',
        'type' => 'type',
        'teamId' => 'team.id',
        'vehicleId' => 'repairVehicle.id',
        'regNo' => 'repairVehicle.regNo',
        'assetId' => 'repairAsset.id',
        'srVehicleId' => 'serviceRecordVehicle.id',
        'repairTitle' => 'repairTitle',
        'repairCategory' => 'repairCategoryEntity.id',
        'user' => 'createdBy.fullName'
    ];
    public const ELASTIC_RANGE_FIELDS = [
        'cost' => 'cost',
        'date' => 'date'
    ];

    public const FIELDS_TO_CONVERT = ['sr_date', 'sr_last_date'];

    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        TransformedFinder $serviceRecordFinder,
        EventDispatcherInterface $eventDispatcher,
        ValidationService $validationService,
        FileService $fileService,
        EventDispatcher $notificationDispatcher,
        VehicleService $vehicleService,
        RedisService $dashboardCache,
        SlaveEntityManager $emSlave
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->serviceRecordFinder = new ElasticSearch($serviceRecordFinder);
        $this->eventDispatcher = $eventDispatcher;
        $this->validationService = $validationService;
        $this->fileService = $fileService;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->vehicleService = $vehicleService;
        $this->dashboardCache = $dashboardCache;
        $this->emSlave = $emSlave;
    }

    public function create(array $data, User $currentUser, Reminder $reminder = null): ServiceRecord
    {
        if ($reminder) {
            $data['reminder'] = $reminder;
        }
        $this->validateServiceRecordFields($data, $currentUser);

        try {
            if ($reminder && $reminder->getDraftRecord()) {
                $serviceRecord = $reminder->getDraftRecord();
                $serviceRecord->setStatus(ServiceRecord::STATUS_ACTIVE);
                $data['updatedAt'] = new \DateTime();
                $data['updatedBy'] = $currentUser;
                $data['createdBy'] = $currentUser;
                $serviceRecord->setAttributes($data);
            } else {
                $serviceRecord = new ServiceRecord($data);
                $serviceRecord->setCreatedBy($currentUser);
                $serviceRecord->setReminder($data['reminder']);
            }

            if ($data['date']) {
                $serviceRecord->setDate(self::parseDateToUTC($data['date']));
            } else {
                $serviceRecord->setDate(null);
            }

            $serviceRecord = $this->removeFiles($data, $serviceRecord);
            $serviceRecord = $this->addFiles($data, $serviceRecord, $currentUser);

            $reminder = $serviceRecord->getReminder();
            $lastServiceDate = $reminder->getLastActiveRecord()?->getDate();

            if ($serviceRecord->isActive() && (!$lastServiceDate || $lastServiceDate < $serviceRecord->getDate())) {
                $reminder = $this->updateControlValues($reminder, $serviceRecord);
            }

            $this->em->persist($serviceRecord);
            $this->em->flush();

            if ($serviceRecord ?? null) {
                $this->eventDispatcher->dispatch(
                    new ServiceRecordCreatedEvent($serviceRecord),
                    ServiceRecordCreatedEvent::NAME
                );

                $this->notificationDispatcher->dispatch(Event::SERVICE_RECORD_ADDED, $serviceRecord);
            }

            return $serviceRecord;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function createRepair(array $data, User $currentUser): ServiceRecord
    {
        $this->validateRepairFields($data, $currentUser);

        try {
            $serviceRecord = new ServiceRecord($data);
            $serviceRecord = $this->handleCreateRepairFields($data, $serviceRecord, $currentUser);
            $serviceRecord = $this->removeFiles($data, $serviceRecord);
            $serviceRecord = $this->addFiles($data, $serviceRecord, $currentUser);

            $this->em->persist($serviceRecord);
            $this->em->persist($serviceRecord->getRepairData());
            $this->em->flush();

            if ($serviceRecord ?? null) {
                $this->eventDispatcher->dispatch(new RepairCreatedEvent($serviceRecord), RepairCreatedEvent::NAME);

                $this->notificationDispatcher->dispatch(Event::SERVICE_REPAIR_ADDED, $serviceRecord);
            }

            return $serviceRecord;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function editRepair(array $data, User $currentUser, ServiceRecord $serviceRecord): ServiceRecord
    {
        $this->validateRepairFields($data, $currentUser);
        try {
            $this->handleEditRepairFields($data, $serviceRecord, $currentUser);

            $this->em->flush();

            if ($serviceRecord ?? null) {
                $this->eventDispatcher->dispatch(new RepairUpdatedEvent($serviceRecord), RepairUpdatedEvent::NAME);
            }
            return $serviceRecord;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function removeRepair(ServiceRecord $serviceRecord)
    {
        try {
            $serviceRecord->setStatus(ServiceRecord::STATUS_DELETED);
            $this->em->flush();

            $this->eventDispatcher->dispatch(new RepairDeletedEvent($serviceRecord), RepairDeletedEvent::NAME);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function serviceRecordList(array $params, User $user, bool $paginated = true)
    {
        $params['type'] = ServiceRecord::TYPE_SERVICE_RECORD;
        if (isset($params['status'])) {
            $params['status'] = $params['status'] === ServiceRecord::STATUS_ALL ? ServiceRecord::ALLOWED_STATUSES : $params['status'];
        } else {
            $params['status'] = ServiceRecord::LIST_STATUSES;
        }
        $fields = $this->prepareElasticFields($params);

        return $this->serviceRecordFinder->find($fields, $fields['_source'] ?? [], $paginated);
    }

    public function repairList(
        array $params,
        User $user,
        ?Vehicle $vehicle = null,
        bool $paginated = true,
        ?Asset $asset = null
    ) {
        $params['type'] = ServiceRecord::TYPE_REPAIR;
        $params = UserServiceHelper::handleTeamParams($params, $user);

        if (isset($params['status'])) {
            $params['status'] = $params['status'] === ServiceRecord::STATUS_ALL ? ServiceRecord::ALLOWED_STATUSES : $params['status'];
        } else {
            $params['status'] = ServiceRecord::LIST_STATUSES;
        }

        if ($vehicle) {
            $params['vehicleId'] = $vehicle->getId();
        }
        if ($asset) {
            $params['assetId'] = $asset->getId();
        }

        $fields = $this->prepareElasticFields($params);

        return $this->serviceRecordFinder->find($fields, $fields['_source'] ?? [], $paginated);
    }

    /**
     * @param array $params
     * @param User $user
     * @return array
     */
    public function repairsVehicles(array $params, User $user)
    {
        $vehicleIds = $this->getRepairsVehicleIds($params, $user);
        $listParams = array_merge($params, ['id' => $vehicleIds]);

        return $this->vehicleService->vehicleList($listParams, $user, true, Vehicle::REPORT_VALUES);
    }

    public function getRepairsVehicleIds(array $params, User $user)
    {
        $params = UserServiceHelper::handleTeamParams($params, $user);

        return array_column(
            $this->em->getRepository(ServiceRecord::class)
                ->getServiceDetailed(MaintenanceReportHelper::prepareFields($params), ServiceRecord::TYPE_REPAIR, true)
                ->execute()->fetchAll()
            ,
            'id'
        );
    }

    /**
     * @param array $params
     * @param User $user
     * @return array
     */
    public function serviceRecordsVehicles(array $params, User $user)
    {
        $vehicleIds = $this->getServiceRecordsVehicleIds($params, $user);
        $listParams = array_merge($params, ['id' => $vehicleIds]);

        return $this->vehicleService->vehicleList($listParams, $user, true, Vehicle::REPORT_VALUES);
    }

    public function getServiceRecordsVehicleIds(array $params, User $user)
    {
        $params = UserServiceHelper::handleTeamParams($params, $user);

        return array_column(
            $this->em->getRepository(ServiceRecord::class)
                ->getServiceDetailed(MaintenanceReportHelper::prepareFields($params),
                    ServiceRecord::TYPE_SERVICE_RECORD, true)->execute()->fetchAll(),
            'id'
        );
    }

    /**
     * @param array $params
     * @param User $user
     * @return array
     */
    public function commonVehicles(array $params, User $user)
    {
        $vehicleIds = $this->getCommonVehicleIds($params, $user);
        $listParams = array_merge($params, ['id' => $vehicleIds]);

        return $this->vehicleService->vehicleList($listParams, $user, true, Vehicle::REPORT_VALUES);
    }

    public function getCommonVehicleIds(array $params, User $user)
    {
        $params = UserServiceHelper::handleTeamParams($params, $user);

        return array_column(
            $this->em->getRepository(ServiceRecord::class)
                ->getServiceDetailed(MaintenanceReportHelper::prepareFields($params), null, true)
                ->execute()->fetchAll(),
            'id'
        );
    }

    /**
     * @param int $id
     * @param User $currentUser
     * @return object|null
     */
    public function getById(int $id, User $currentUser)
    {
        return $this->em->getRepository(ServiceRecord::class)->getServiceRecordById($id, $currentUser);
    }

    /**
     * @param int $serviceRecordId
     * @param int $reminderId
     * @return object|ServiceRecord|null
     */
    public function getByReminderIdAndServiceRecordId(int $serviceRecordId, int $reminderId)
    {
        return $this->em->getRepository(ServiceRecord::class)
            ->findByServiceRecordIdAndReminderId($serviceRecordId, $reminderId);
    }

    public function edit(array $data, User $currentUser, ServiceRecord $serviceRecord): ServiceRecord
    {
        $data['reminder'] = $serviceRecord->getReminder();
        $this->validateServiceRecordFields($data, $currentUser);

        try {
            $data['updatedAt'] = new \DateTime();
            $data['updatedBy'] = $currentUser;
            $serviceRecord->setAttributes($data);

            if ($data['date'] ?? null) {
                $serviceRecord->setDate(self::parseDateToUTC($data['date']));
            }

            $serviceRecord = $this->removeFiles($data, $serviceRecord);
            $serviceRecord = $this->addFiles($data, $serviceRecord, $currentUser);

            $this->em->flush();

            if ($serviceRecord ?? null) {
                $this->eventDispatcher->dispatch(
                    new ServiceRecordUpdatedEvent($serviceRecord), ServiceRecordUpdatedEvent::NAME
                );
            }
            return $serviceRecord;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param ServiceRecord $serviceRecord
     * @throws \Exception
     */
    public function removeServiceRecord(ServiceRecord $serviceRecord)
    {
        try {
            $serviceRecord->setStatus(ServiceRecord::STATUS_DELETED);
            $this->em->flush();

            $this->eventDispatcher->dispatch(
                new ServiceRecordDeletedEvent($serviceRecord),
                ServiceRecordDeletedEvent::NAME
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateStatus(Reminder $reminder, string $status = Reminder::STATUS_DRAFT): Reminder
    {
        if (!in_array($status, Reminder::ALLOWED_STATUSES, true)) {
            throw new \Exception('Invalid status');
        }
        $statuses = [];
        if ($reminder->getDateCheckbox()) {
            $statuses[] = $this->calculateReminderStatusByDate($reminder, $status);
        }

        if ($reminder->getMileageCheckbox()) {
            $statuses[] = $this->calculateReminderStatusByMileage($reminder, $status);
        }

        if ($reminder->getHoursCheckbox()) {
            $statuses[] = $this->calculateReminderStatusByEngineHours($reminder, $status);
        }

        $reminder->setStatus($status);

        if (in_array(Reminder::STATUS_EXPIRED, $statuses)) {
            return $reminder->setStatus(Reminder::STATUS_EXPIRED);
        }

        if (in_array(Reminder::STATUS_DUE_SOON, $statuses)) {
            return $reminder->setStatus(Reminder::STATUS_DUE_SOON);
        }

        if (in_array(Reminder::STATUS_ACTIVE, $statuses)) {
            return $reminder->setStatus(Reminder::STATUS_ACTIVE);
        }

        if (in_array(Reminder::STATUS_DONE, $statuses)) {
            return $reminder->setStatus(Reminder::STATUS_DONE);
        }

        return $reminder;
    }

    /**
     * @param Reminder $reminder
     * @param string $status
     * @return string
     * @throws \Exception
     */
    protected function calculateReminderStatusByDate(Reminder $reminder, string $status = Reminder::STATUS_DRAFT)
    {
        if (!$reminder->getDatePeriod() && $reminder->getStatus() === Reminder::STATUS_DONE) {
            return Reminder::STATUS_DONE;
        }

        $nowTs = (new \DateTime())->getTimestamp();

        $notifyDays = $reminder->getDateNotification();
        $notifyDate = $reminder->getDate() && $notifyDays ? clone $reminder->getDate() : null;

        //if exists notification days - calculate date for notification
        if ($reminder->getDate() && !is_null($notifyDays) && $notifyDate) {
            $notifyDate->sub(new \DateInterval('P' . $notifyDays . 'D'));
        }

        if (is_null($reminder->getDate()) ||
            ($nowTs < $reminder->getDate()->getTimestamp() && (!$notifyDate || $nowTs <= $notifyDate->getTimestamp()))) {
            $status = Reminder::STATUS_ACTIVE;
        }

        //if exists notification days and reminder is not repeat
        if ($reminder->getDate() && $notifyDate && $reminder->getStatus() !== Reminder::STATUS_DUE_SOON
            && $nowTs >= $notifyDate->getTimestamp() && $nowTs < $reminder->getDate()->getTimestamp()) {
            $status = Reminder::STATUS_DUE_SOON;
            $this->notificationDispatcher->dispatch(Event::SERVICE_REMINDER_SOON, $reminder, null, ['type' => 'date']);
        }

        if ($reminder->getDate() && $nowTs >= $reminder->getDate()->getTimestamp()
            && $reminder->getStatus() !== Reminder::STATUS_EXPIRED) {
            $this->notificationDispatcher->dispatch(Event::SERVICE_REMINDER_EXPIRED, $reminder);
            $status = Reminder::STATUS_EXPIRED;
        }

        return $status;
    }

    protected function calculateReminderStatusByMileage(Reminder $reminder, string $status = Reminder::STATUS_DRAFT)
    {
        if (!$reminder->getMileagePeriod() && $reminder->getStatus() === Reminder::STATUS_DONE) {
            return Reminder::STATUS_DONE;
        }

        $nowOdometer = $reminder->getCurrentOdometer();

        $notifyEveryMileageCount = $reminder->getMileageNotification();
        $notifyMileage = $reminder->getMileage() && $notifyEveryMileageCount ? $reminder->getMileage() : null;

        if ($reminder->getMileage() && !is_null($notifyEveryMileageCount) && $notifyMileage) {
            $notifyMileage -= $notifyEveryMileageCount;
        }

        if (is_null($reminder->getMileage())
            || ($nowOdometer < $reminder->getMileage() && (!$notifyMileage || $nowOdometer <= $notifyMileage))) {
            $status = Reminder::STATUS_ACTIVE;
        }

        if ($reminder->getMileage() && $notifyMileage && $nowOdometer >= $notifyMileage
            && $nowOdometer < $reminder->getMileage() && $reminder->getStatus() !== Reminder::STATUS_DUE_SOON) {
            $this->notificationDispatcher->dispatch(Event::SERVICE_REMINDER_SOON, $reminder, null,
                ['type' => 'mileage', 'mileage' => $reminder->getMileage() - $nowOdometer]);
            $status = Reminder::STATUS_DUE_SOON;
        }

        if ($reminder->getMileage() && $nowOdometer >= $reminder->getMileage()
            && $reminder->getStatus() !== Reminder::STATUS_EXPIRED) {
            $this->notificationDispatcher->dispatch(Event::SERVICE_REMINDER_EXPIRED, $reminder);
            $status = Reminder::STATUS_EXPIRED;
        }

        return $status;
    }

    protected function calculateReminderStatusByEngineHours(Reminder $reminder, string $status = Reminder::STATUS_DRAFT)
    {
        if (!$reminder->getHoursPeriod() && $reminder->getStatus() === Reminder::STATUS_DONE) {
            return Reminder::STATUS_DONE;
        }

        $nowEngineHours = $reminder->getVehicle()->getEngineOnTime()
            ? round($reminder->getVehicle()->getEngineOnTime() / 3600, 1)
            : 0;

        $notifyEveryEngineHoursCount = $reminder->getHoursNotification();
        $notifyEngineHours = $reminder->getHours() && $notifyEveryEngineHoursCount ? $reminder->getHours() : null;

        if ($reminder->getHours() && !is_null($notifyEveryEngineHoursCount) && $notifyEngineHours) {
            $notifyEngineHours -= $notifyEveryEngineHoursCount;
        }

        if (is_null($reminder->getHours())
            || ($nowEngineHours < $reminder->getHours() && (!$notifyEngineHours || $nowEngineHours <= $notifyEngineHours))) {
            $status = Reminder::STATUS_ACTIVE;
        }

        if ($reminder->getHours() && $notifyEngineHours && $nowEngineHours >= $notifyEngineHours
            && $nowEngineHours < $reminder->getHours() && $reminder->getStatus() !== Reminder::STATUS_DUE_SOON) {
            $this->notificationDispatcher->dispatch(Event::SERVICE_REMINDER_SOON, $reminder, null,
                ['type' => 'engineHours', 'engineHours' => $reminder->getHours() - $nowEngineHours]);
            $status = Reminder::STATUS_DUE_SOON;
        }

        if ($reminder->getHours() && $nowEngineHours >= $reminder->getHours()
            && $reminder->getStatus() !== Reminder::STATUS_EXPIRED) {
            $this->notificationDispatcher->dispatch(Event::SERVICE_REMINDER_EXPIRED, $reminder);
            $status = Reminder::STATUS_EXPIRED;
        }

        return $status;
    }

    /**
     * @param Reminder $reminder
     * @return Reminder
     * @throws \Exception
     */
    public function updateControlValues(Reminder $reminder, ServiceRecord $serviceRecord): Reminder
    {
        if ($reminder->getDatePeriod() && $reminder->getDate()) {
            $newControlDate = (new \DateTime())
                ->add(new \DateInterval('P' . $reminder->getDatePeriod() . 'D'));
            $reminder->setDate($newControlDate);
        }

        if ($serviceRecord->getOdometer() && $reminder->getMileagePeriod() && !$reminder->isFixedMileage()) {
            $reminder->setMileage($serviceRecord->getOdometer() + $reminder->getMileagePeriod());
        } elseif ($reminder->getMileagePeriod()) {
            $reminder->setMileage($reminder->getMileage() + $reminder->getMileagePeriod());
        }

        if ($serviceRecord->getEngineHours() && $reminder->getHoursPeriod() && !$reminder->isFixedMileage()) {
            $reminder->setHours($serviceRecord->getEngineHours() + $reminder->getHoursPeriod());
        } elseif ($reminder->getHoursPeriod()) {
            $reminder->setHours($reminder->getHours() + $reminder->getHoursPeriod());
        }

        if ($reminder->getDatePeriod() || $reminder->getMileagePeriod() || $reminder->getHoursPeriod()) {
            $reminder = $this->updateStatus($reminder);
        } else {
            $reminder->setStatus(Reminder::STATUS_DONE);
            $this->notificationDispatcher->dispatch(Event::SERVICE_REMINDER_DONE, $reminder);
        }

        return $reminder;
    }

    /**
     * @param array $data
     * @param ServiceRecord $serviceRecord
     * @return ServiceRecord
     * @throws \Doctrine\ORM\ORMException
     */
    private function removeFiles(array $data, ServiceRecord $serviceRecord)
    {
        if ($data['removeFiles'] ?? null) {
            $serviceRecord->removeFiles($data['removeFiles']);

            foreach ($data['removeFiles'] as $fileId) {
                $file = $this->em->getRepository(File::class)->find($fileId);

                if ($file) {
                    $this->fileService->delete($file);
                    $this->em->remove($file);
                }
            }
        }

        return $serviceRecord;
    }

    /**
     * @param array $data
     * @param ServiceRecord $serviceRecord
     * @param User $currentUser
     * @return ServiceRecord
     */
    private function addFiles(array $data, ServiceRecord $serviceRecord, User $currentUser)
    {
        if (isset($data['files']) && $data['files']->get('files') ?? null) {
            foreach ($data['files']->get('files') as $file) {
                $fileEntity = $this->fileService->uploadServiceRecordFile($file, $currentUser);
                $serviceRecord->addFile($fileEntity);
            }
        }

        return $serviceRecord;
    }

    /**
     * @param User $user
     * @param int $days
     * @return array|mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getTeamRepairStat(User $user, int $days = 7)
    {
        $redisKey = 'teamRepairStat-' . $user->getTeam()->getId() . 'days-' . $days;
        $redisData = $this->dashboardCache->getFromJson($redisKey);
        if ($redisData) {
            return $redisData;
        }

        $startDate = (new Carbon())->subDays($days);
        $endDate = new Carbon();

        $prevStartDate = (new Carbon())->subDays(2 * $days);
        $prevEndDate = (new Carbon())->subDays($days);

        $vehicles = $this->em->getRepository(ServiceRecord::class)
            ->getExpensiveVehiclesByTeam($user, $startDate, $endDate, 3);

        $data = [
            'totalCost' => [
                'current' => [
                    'startDate' => DateHelper::formatDate($startDate),
                    'endDate' => DateHelper::formatDate($endDate),
                    'cost' => $this->em->getRepository(ServiceRecord::class)
                        ->getRepairsCost($user, $startDate, $endDate)

                ],
                'prev' => [
                    'startDate' => DateHelper::formatDate($prevStartDate),
                    'endDate' => DateHelper::formatDate($prevEndDate),
                    'cost' => $this->em->getRepository(ServiceRecord::class)
                        ->getRepairsCost($user, $prevStartDate, $prevEndDate)
                ]
            ],
            'vehicles' => $vehicles
        ];

        $this->dashboardCache->setToJsonTtl($redisKey, $data, 300);

        return $data;
    }

    public function getRepairListByVehicleExportData($params, Vehicle $vehicle, User $user)
    {
        $serviceRecords = $this->repairList(
            array_merge($params, ['vehicleId' => $vehicle->getId()]),
            $user,
            $vehicle,
            false
        );

        return $this->translateEntityArrayForExport($serviceRecords, $params['fields']);
    }

    public function getRepairListByAssetExportData($params, Asset $asset, User $user)
    {
        $serviceRecords = $this->repairList(
            array_merge($params, ['assetId' => $asset->getId()]),
            $user,
            null,
            false,
            $asset
        );

        return $this->translateEntityArrayForExport($serviceRecords, $params['fields']);
    }

    public function getRepairListExportData($params, User $user)
    {
        $serviceRecords = $this->repairList($params, $user, null, false);

        return $this->translateEntityArrayForExport($serviceRecords, $params['fields']);
    }
}