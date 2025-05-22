<?php

namespace App\Tests\Behat\Context\Traits;

use App\Entity\DeviceInstallation;
use App\Entity\DeviceModel;
use App\Entity\DriverHistory;
use App\Entity\Role;
use App\Entity\Tracker\TrackerHistorySensor;
use App\Entity\User;
use App\Entity\Vehicle;
use App\EventListener\Device\DeviceListener;
use App\Events\Device\DeviceEngineOnTimeEvent;
use App\Events\Device\DevicePanicButtonEvent;
use App\Events\Device\DeviceTowingEvent;
use App\Events\Device\DeviceVoltageEvent;
use App\Events\Device\OverSpeedingEvent;
use App\Service\Area\AreaService;
use App\Service\Device\DeviceOverSpeedingQueue\DeviceOverSpeedingConsumer;
use App\Service\Device\DeviceOverSpeedingQueue\DeviceOverSpeedingQueueMessage;
use App\Service\Device\DeviceQueue\EngineOnTime\EngineOnTimeConsumer;
use App\Service\Device\DeviceQueue\EngineOnTime\EngineOnTimeQueueMessage;
use App\Service\Device\DeviceQueue\PanicButton\PanicButtonConsumer;
use App\Service\Device\DeviceQueue\PanicButton\PanicButtonQueueMessage;
use App\Service\Device\DeviceSensorQueue\DeviceSensorConsumer;
use App\Service\Device\DeviceTowingQueue\DeviceTowingConsumer;
use App\Service\Device\DeviceTowingQueue\DeviceTowingQueueMessage;
use App\Service\Device\DeviceVoltageQueue\DeviceVoltageConsumer;
use App\Service\Device\DeviceVoltageQueue\DeviceVoltageQueueMessage;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Notification\EventDispatcher;
use App\Service\Route\SetDriverInRelatedEntriesConsumer;
use Carbon\Carbon;
use DateTime;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

trait VehicleTrait
{
    protected $vehicleData;
    protected $documentData;
    protected $deviceData;
    protected $driverId;

    /**
     * @param string $event
     * @return \Mockery\Mock
     */
    private function getDeviceListenerMockByEvent(string $event)
    {
        $entityHistoryService = \Mockery::mock(
            EntityHistoryService::class,
            [new TokenStorage(), $this->getEntityManager()]
        )->makePartial();
        $deviceService = \Mockery::mock($this->getContainer()->get('App\Service\Device\DeviceService'));
        $vehicleService = \Mockery::mock($this->getContainer()->get('App\Service\Vehicle\VehicleService'));
        $voltageProducer = \Mockery::mock($this->getContainer()->get('old_sound_rabbit_mq.tracker_voltage_producer'));
        $towingProducer = \Mockery::mock($this->getContainer()->get('old_sound_rabbit_mq.tracker_towing_producer'));
        $panicButtonProducer = \Mockery::mock(
            $this->getContainer()->get('old_sound_rabbit_mq.tracker_panic_button_producer')
        );
        $overSpeedingProducer = \Mockery::mock(
            $this->getContainer()->get('old_sound_rabbit_mq.tracker_overspeeding_producer')
        );
        $engineOnTimeProducer = \Mockery::mock(
            $this->getContainer()->get('old_sound_rabbit_mq.tracker_engine_on_time_producer')
        );
        $engineOnTimeMultipleProducer = \Mockery::mock(
            $this->getContainer()->get('old_sound_rabbit_mq.tracker_engine_on_time_multiple_producer')
        );
        $ioProducer = \Mockery::mock(
            $this->getContainer()->get('old_sound_rabbit_mq.tracker_io_producer')
        );
        $notificationDispatcher = new EventDispatcher(
            $this->getContainer()->get('event_dispatcher'),
            new TokenStorage()
        );


        return \Mockery::mock(
            sprintf('%s[%s]', DeviceListener::class, $event),
            [
                $this->getEntityManager(),
                $entityHistoryService,
                new TokenStorage(),
                $deviceService,
                $vehicleService,
                $voltageProducer,
                $towingProducer,
                $panicButtonProducer,
                $overSpeedingProducer,
                $engineOnTimeProducer,
                $engineOnTimeMultipleProducer,
                $ioProducer,
                $notificationDispatcher
            ]
        )->makePartial();
    }

    /**
     * @When I want to create vehicle and save id
     */
    public function iWantToCreateVehicle()
    {
        $this->post(
            '/api/vehicles',
            $this->fillData,
            [
                'CONTENT_TYPE' => 'multipart/form-data',
            ],
            $this->files
        );

        $this->vehicleData = json_decode($this->getResponse()->getContent());
    }

    /**
     * @When I want fill vehicle ids with saved id
     */
    public function iWantFillVehicleIdsSavedId()
    {
        $this->fillData['vehicleIds'][] = $this->vehicleData->id;
    }

    /**
     * @When I want fill vehicle ids with empty array
     */
    public function iWantFillVehicleIdsEmptyArray()
    {
        $this->fillData['vehicleIds'] = [];
    }

    /**
     * @When I want set vehicle driver with current user
     */
    public function iWantSetVehicleDriverCurrentUser()
    {
        $this->post(
            '/api/vehicles/' . $this->vehicleData->id . '/set-driver/' . $this->authorizedUser->getId(),
            $this->fillData
        );
    }

    /**
     * @When I want set vehicle driver with current user and date :date
     */
    public function iWantSetVehicleDriverCurrentUserAndDate($date)
    {
        $this->fillData['startDate'] = $date;
        $this->post(
            '/api/vehicles/' . $this->vehicleData->id . '/set-driver/' . $this->authorizedUser->getId(),
            $this->fillData
        );
    }

    /**
     * @When I want set vehicle driver with not current user
     */
    public function iWantSetVehicleDriverWithNotCurrentUser()
    {
        $users = $this->getEntityManager()->getRepository(User::class)->findAll();

        /** @var \App\Entity\user $user */
        foreach ($users as $user) {
            if ($user->getId() != $this->authorizedUser->getId()) {
                $notLoggedUserId = $user->getId();
                break;
            }
        }

        $this->post('/api/vehicles/' . $this->vehicleData->id . '/set-driver/' . $notLoggedUserId, $this->fillData);
    }

    /**
     * @When I want unset vehicle driver with current user
     */
    public function iWantUnsetVehicleDriverCurrentUser()
    {
        $this->post(
            '/api/vehicles/' . $this->vehicleData->id . '/unset-driver/' . $this->authorizedUser->getId(),
            $this->fillData
        );
    }

    /**
     * @When I want set vehicle driver with current user with date :date
     */
    public function iWantSetVehicleDriverCurrentUserWithDate($date)
    {
        $this->post(
            '/api/vehicles/' . $this->vehicleData->id . '/set-driver/' . $this->authorizedUser->getId(),
            $this->fillData
        );

        $em = $this->getEntityManager();

        $vehicle = $em->getRepository(Vehicle::class)->find($this->vehicleData->id);

        $criteria = [
            'vehicle' => $vehicle,
            'driver' => $this->authorizedUser,
        ];
        $driverHistory = $em->getRepository(DriverHistory::class)->findOneBy($criteria);
        $driverHistory->setStartDate(new \DateTime($date));

        $em->flush();
    }

    /**
     * @When I want set vehicle driver with user of current team with date :date
     */
    public function iWantSetVehicleDriverWithUserCurrentTeam($date)
    {
        $em = $this->getEntityManager();
        $driverRole = $em->getRepository(Role::class)->findOneBy(['name' => Role::ROLE_CLIENT_DRIVER]);

        if ($driverRole) {
            $user = $em->getRepository(User::class)->findOneBy(
                [
                    'team' => $this->clientData->team->id,
                    'role' => $driverRole
                ]
            );
        }

        $user = $user ?? $em->getRepository(User::class)->findOneBy(
                [
                    'team' => $this->clientData->team->id
                ]
            );

        $this->post('/api/vehicles/' . $this->vehicleData->id . '/set-driver/' . $user->getId(), $this->fillData);
        $this->driverId = $user->getId();

        $vehicle = $em->getRepository(Vehicle::class)->find($this->vehicleData->id);

        $criteria = [
            'vehicle' => $vehicle,
            'driver' => $user,
        ];
        $driverHistory = $em->getRepository(DriverHistory::class)->findOneBy($criteria);
        $driverHistory->setStartDate(new \DateTime($date));

        $em->flush();
    }

    /**
     * @When I want unset vehicle driver with current user with date :date
     */
    public function iWantUnsetVehicleDriverCurrentUserWithDate($date)
    {
        $this->post(
            '/api/vehicles/' . $this->vehicleData->id . '/unset-driver/' . $this->driverId,
            $this->fillData
        );

        $vehicle = $this->getEntityManager()->getRepository(Vehicle::class)->find($this->vehicleData->id);
        $driver = $this->getEntityManager()->getRepository(User::class)->find($this->driverId);
        $criteria = [
            'vehicle' => $vehicle,
            'driver' => $driver
        ];
        /** @var DriverHistory $driverHistory */
        $driverHistory = $this->getEntityManager()->getRepository(DriverHistory::class)->findOneBy($criteria);
        $driverHistory->setFinishDate(new \DateTime($date));

        $this->getEntityManager()->flush();
    }

    /**
     * @When I want to get vehicle by saved id
     */
    public function iWantGetVehicleBySavedId()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/vehicles/' . $this->vehicleData->id . '?' . $params);
    }

    /**
     * @When I want to get vehicle by model :model
     */
    public function iWantGetVehicleByModel($model)
    {
        $vehicle = $this->getEntityManager()->getRepository(Vehicle::class)->findOneBy(['model' => $model]);
        $this->get('/api/vehicles/' . $vehicle->getId());
    }

    /**
     * @When I want to edit vehicle by saved id
     */
    public function iWantEditVehicleBySavedId()
    {
        $this->post(
            '/api/vehicles/' . $this->vehicleData->id,
            $this->fillData,
            ['CONTENT_TYPE' => 'multipart/form-data'],
            $this->files
        );
    }

    /**
     * @When I want to delete vehicle by saved id
     */
    public function iWantDeleteVehicleBySavedId()
    {
        $this->delete('/api/vehicles/' . $this->vehicleData->id);
    }

    /**
     * @When I want get vehicle list
     */
    public function iWantGeVehicleList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/vehicles/json?' . $params);
    }

    /**
     * @When I want export vehicles list
     */
    public function iWantExportVehiclesList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/vehicles/csv?' . $params);
    }

    /**
     * @When I want fill vehicle id
     */
    public function iWantFillVehicleId()
    {
        $this->fillData['vehicleId'] = $this->vehicleData->id;
    }

    /**
     * @When I want fill driver id of current team
     */
    public function iWantFillDriverIdCurrentTeam()
    {
        $em = $this->getEntityManager();
        $driverRole = $em->getRepository(Role::class)->findOneBy(['name' => Role::ROLE_CLIENT_DRIVER]);

        $this->driver = $em->getRepository(User::class)->findOneBy(
            [
                'team' => $this->clientData->team->id,
                'role' => $driverRole
            ]
        );

        $this->fillData['driverId'] = $this->driver->getId();
    }

    /**
     * @When I want fill driver id
     */
    public function iWantFillDriverId()
    {
        $this->fillData['driver_id'] = $this->driverId;
    }

    /**
     * @When I want create document
     */
    public function iWantCreateDocument()
    {
        $this->post(
            '/api/documents',
            $this->fillData,
            ['CONTENT_TYPE' => 'multipart/form-data'],
            ['files' => $this->files]
        );
        $this->documentData = json_decode($this->getResponse()->getContent());
    }

    /**
     * @When I want get document
     */
    public function iWantGetDocument()
    {
        $this->get(sprintf('/api/documents/%d', $this->documentData->id));
        $this->documentData = json_decode($this->getResponse()->getContent());
    }

    /**
     * @When I want get documents list
     */
    public function iWantGetDocumentsListForCurrentVehicle()
    {
        $this->get(sprintf('/api/vehicles/%d/documents', $this->vehicleData->id));
    }

    /**
     * @When I want get driver documents list
     */
    public function iWantGetDriverDocumentsListForCurrentVehicle()
    {
        $this->get(sprintf('/api/drivers/%d/documents', $this->driver->getId()));
    }

    /**
     * @When I want export driver documents list
     */
    public function iWantExportDriverDocumentsListForCurrentVehicle()
    {
        $params = http_build_query($this->fillData);
        return $this->get('/api/drivers/' . $this->driver->getId() . '/documents/csv?' . $params);
    }

    /**
     * @When I want export driver full documents list
     */
    public function iWantExportDriverFullDocumentsList()
    {
        $params = http_build_query($this->fillData);
        return $this->get('/api/drivers/documents/csv?' . $params);
    }

    /**
     * @When I want get driver full documents list
     */
    public function iWantGetDriverFullDocumentsList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/drivers/documents?' . $params);
    }

    /**
     * @When I want export documents list
     */
    public function iWantExportDocumentsListForCurrentVehicle()
    {
        $params = http_build_query($this->fillData);
        return $this->get('/api/vehicles/' . $this->vehicleData->id . '/documents/csv?' . $params);
    }

    /**
     * @When I want export full documents list
     */
    public function iWantExportFullDocumentsList()
    {
        $params = http_build_query($this->fillData);
        return $this->get('/api/vehicles/documents/csv?' . $params);
    }

    /**
     * @When I want get full documents list
     */
    public function iWantGetFullDocumentsList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/vehicles/documents?' . $params);
    }

    /**
     * @When I want update document
     */
    public function iWantUpdateDocument()
    {
        $response = $this->post(
            sprintf('/api/documents/%d', $this->documentData->id),
            $this->fillData,
            ['CONTENT_TYPE' => 'multipart/form-data'],
            ['files' => $this->files]
        );

        if ($response->getResponse()->getStatusCode() === 200) {
            $this->fileData = json_decode(
                $response->getResponse()->getContent()
            );
        }
    }

    /**
     * @When I want upgrade document
     */
    public function iWantUpgradeDocument()
    {
        $this->post(sprintf('/api/documents/%d/upgrade', $this->documentData->id));
    }

    /**
     * @When I want set modified date :field :modifier
     */
    public function iWantSetExpiredDate($field, $modifier)
    {
        $this->fillData[$field] = (new \DateTime())->modify($modifier)->format('Y-m-d H:i:s');
    }

    /**
     * @When I want delete document
     */
    public function iWantDeleteDocument()
    {
        $this->delete(sprintf('/api/documents/%d', $this->documentData->id));
    }

    /**
     * @When I want to create device for vehicle and save id
     */
    public function iWantToCreateDevice()
    {
        $this->post('/api/devices', $this->fillData);
        $this->deviceData = json_decode($this->getResponse()->getContent());
    }

    /**
     * @When I want install device for vehicle
     */
    public function iWantInstallDevice()
    {
        $this->post(
            '/api/devices/' . $this->deviceData->id . '/install',
            [
                'vehicleId' => $this->vehicleData->id,
            ]
        );
    }

    /**
     * @When I want fill device model for vehicle with name :model
     */
    public function iWantFillDeviceModel($model)
    {
        $model = $this->getEntityManager()->getRepository(DeviceModel::class)->findOneBy(['name' => $model]);
        $this->fillData['modelId'] = $model->getId();
    }

    /**
     * @When I want upload picture
     */
    public function iWantUploadPicture()
    {
        $this->files['picture'] = new UploadedFile(
            '/srv/features/files/test_image.png',
            'test_file.png',
            'image/png',
            UPLOAD_ERR_OK,
            true
        );
    }

    /**
     * @When I want to get device of vehicle by saved id
     */
    public function iWantGetDeviceBySavedId()
    {
        $this->get('/api/devices/' . $this->deviceData->id);
    }

    /**
     * @When I want get vehicle history for driver :email
     */
    public function iWantGetVehicleHistory($email)
    {
        /** @var \App\Entity\User $user */
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(['email' => $email]);
        $this->fillData['driverId'] = $user->getId();
        $params = http_build_query($this->fillData);

        $this->get('/api/history/vehicles?' . $params);
    }

    /**
     * @Given Calculate routes
     */
    public function calculateRoutes()
    {
        $application = new Application($this->getKernel());
        $application->setAutoExit(false);

        $input = new ArrayInput(
            [
                'command' => 'app:tracker:calculate-routes'
            ]
        );

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);
    }

    /**
     * @Given Calculate idlings
     */
    public function calculateIdlings()
    {
        $application = new Application($this->getKernel());
        $application->setAutoExit(false);

        $input = new ArrayInput(
            [
                'command' => 'app:tracker:calculate-idling'
            ]
        );

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);
    }

    /**
     * @When I want get current vehicle data from :from to :to
     */
    public function iWantGetCurrentVehicleTodayData($from, $to)
    {
        $params = http_build_query(['dateFrom' => $from, 'dateTo' => $to]);
        $this->get('/api/vehicles/' . $this->vehicleData->id . '/routes-info?' . $params);
    }

    /**
     * @When I want get current vehicle data by group :group and count :count and date :date
     */
    public function iWantGetCurrentVehicleDataByGroupAndCount($group, $count, $date)
    {
        $params = http_build_query(['groupType' => $group, 'groupCount' => $count, 'groupDate' => $date]);
        $this->get('/api/vehicles/' . $this->vehicleData->id . '/routes-info?' . $params);
    }

    /**
     * @When I want get current driver data from :from to :to
     */
    public function iWantGetCurrentDriverTodayData($from, $to)
    {
        $params = http_build_query(['dateFrom' => $from, 'dateTo' => $to]);
        $this->get('/api/drivers/' . $this->authorizedUser->getId() . '/routes-info?' . $params);
    }

    /**
     * @When I want get current driver data by group :group and count :count and date :date
     */
    public function iWantGetCurrentDriverDataByGroupAndCount($group, $count, $date)
    {
        $params = http_build_query(['groupType' => $group, 'groupCount' => $count, 'groupDate' => $date]);
        $this->get('/api/drivers/' . $this->authorizedUser->getId() . '/routes-info?' . $params);
    }

    /**
     * @When I want uninstall device
     */
    public function iWantUninstallDevice()
    {
        $this->post(
            '/api/devices/' . $this->deviceData->id . '/uninstall',
            array_merge(['vehicleId' => $this->vehicleData->id], $this->fillData)
        );
    }

    /**
     * @When I want to get vehicle notes by saved id and type :type
     */
    public function iWantGetVehicleNotesBySavedId($type)
    {
        $this->get('/api/vehicle-notes/' . $this->vehicleData->id . '/' . $type);
    }

    /**
     * @Then I want to get vehicle summary list from :from to :to page :page sort :sort
     */
    public function iWantToGetVehicleSummaryList($from, $to, $page, $sort)
    {
        $params['startDate'] = $from;
        $params['endDate'] = $to;
        $params['page'] = $page;
        $params['$sort'] = $sort;

        $paramsAsString = http_build_query($params);
        $paramsAsString = empty($paramsAsString) ?: '?' . $paramsAsString;

        $this->get('/api/reports/vehicle/summary' . $paramsAsString);
    }

    /**
     * @Given Driver :driver starts driving in the vehicle :regNumber at :date
     */
    public function driverStartDrivingInTheVehicle($email, $regNumber, $date)
    {
        $user = $this->getContainer()->get('App\Service\User\UserService')->findUserByEmail($email);
        $vehicle = $this->getContainer()->get('App\Service\Vehicle\VehicleService')->getVehicleBy(
            ['regNo' => $regNumber]
        );

        $data = [
            'driver' => $user,
            'vehicle' => $vehicle,
            'startDate' => new DateTime($date),
        ];

        $em = $this->getEntityManager();
        $em->persist(new DriverHistory($data));
        $em->flush();
    }

    /**
     * @Given Driver :driver starts driving in the current vehicle at :date
     */
    public function driverStartDriving($email, $date)
    {
        $user = $this->getContainer()->get('App\Service\User\UserService')->findUserByEmail($email);
        $vehicle = $this->getContainer()->get('App\Service\Vehicle\VehicleService')->getVehicleBy(
            ['id' => $this->vehicleData->id]
        );

        $data = [
            'driver' => $user,
            'vehicle' => $vehicle,
            'startDate' => new DateTime($date),
        ];

        $em = $this->getEntityManager();
        $em->persist(new DriverHistory($data));
        $em->flush();
    }

    /**
     * @When I want to set following user :email as vehicle driver
     */
    public function IWantToSetFollowingUserAsVehicleDriver($email)
    {
        $criteria = [
            'email' => $email,
        ];
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy($criteria);
        $this->post('/api/vehicles/' . $this->vehicleData->id . '/set-driver/' . $user->getId(), $this->fillData);
    }

    /**
     * @When I want to set following user :email as vehicle driver from date :date
     */
    public function IWantToSetFollowingUserAsVehicleDriverFromDate($email, $date)
    {
        $criteria = [
            'email' => $email,
        ];
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy($criteria);
        $this->post('/api/vehicles/' . $this->vehicleData->id . '/set-driver/' . $user->getId(), $this->fillData);

        $vehicle = $this->getEntityManager()->getRepository(Vehicle::class)->find($this->vehicleData->id);

        $driverHistory = $this->getEntityManager()->getRepository(DriverHistory::class)->findOneBy(
            [
                'vehicle' => $vehicle,
                'driver' => $user,
            ]
        );
        $driverHistory->setStartDate(new \DateTime($date));

        $this->getEntityManager()->flush();
    }

    /**
     * @When I want change installed device date for saved vehicle to :date
     */
    public function iWantChangeInstalledDeviceForSavedVehicleToDate($date)
    {
        /** @var DeviceInstallation $deviceInstallation */
        $deviceInstallation = $this->getEntityManager()->getRepository(DeviceInstallation::class)
            ->findByDeviceAndVehicle($this->deviceData->id, $this->vehicleData->id);

        if ($deviceInstallation) {
            $parsedDate = new Carbon($date);
            $deviceInstallation->setInstallDate($parsedDate);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @When I want fill vehicle groups with saved id
     */
    public function iWantFillVehicleGroupsSavedId()
    {
        $this->fillData['groups'][] = $this->vehicleGroupData->id;
    }

    /**
     * @When I want fill vehicles with saved id
     */
    public function iWantFillVehiclesSavedId()
    {
        $this->fillData['vehicles'][] = $this->vehicleData->id;
    }

    /**
     * @When I want set another vehicle driver with current team with date :date
     */
    public function iWantSetAnotherVehicleDriverWithCurrentTeamWithDate($date)
    {
        $this->fillData['startDate'] = $date;
        $driverRole = $this->getEntityManager()->getRepository(Role::class)
            ->findOneBy(['name' => Role::ROLE_CLIENT_DRIVER]);
        $users = $this->getEntityManager()->getRepository(User::class)->findBy(
            [
                'team' => $this->clientData->team->id,
                'role' => $driverRole
            ]
        );

        /** @var \App\Entity\user $user */
        foreach ($users as $user) {
            if ($user->getId() != $this->driverId) {
                $notLoggedUserId = $user->getId();
                break;
            }
        }

        $this->post('/api/vehicles/' . $this->vehicleData->id . '/set-driver/' . $notLoggedUserId, $this->fillData);
    }

    /**
     * @When I want handle check event when driver update on vehicle
     */
    public function iWantHandleDriverUpdateEvent()
    {
        /** @var SetDriverInRelatedEntriesConsumer $consumer */
        $consumer = $this->getKernel()->getContainer()->get(
            'App\Service\Route\SetDriverInRelatedEntriesConsumer'
        );
        $messageStub = \Mockery::mock(AMQPMessage::class);
        $messageStub
            ->shouldReceive('getBody')
            ->andReturn(
                serialize(
                    $this->getEntityManager()->getRepository(DriverHistory::class)->findOneBy(
                        [],
                        ['startDate' => 'DESC']
                    )
                )
            );

        $consumer->execute($messageStub);
    }

    /**
     * @When I want get documents dashboard statistic
     */
    public function iWantGetDocumentsDashboardStatistic()
    {
        $this->get('/api/dashboard/documents/stat');
    }

    /**
     * @When I want handle check voltage event
     */
    public function iWantHandleCheckVoltageEvent()
    {
        /** @var AreaService $areaService */
        $deviceVoltageConsumerSimulator = \Mockery::mock(
            DeviceVoltageConsumer::class,
            [
                $this->getEntityManager(),
                new EventDispatcher($this->getContainer()->get('event_dispatcher'), new TokenStorage()),
                \Mockery::mock($this->getContainer()->get('logger')),
                'test'
            ]
        )->makePartial();
        $listenerMock = $this->getDeviceListenerMockByEvent('onDeviceVoltageEvent');
        $listenerMock->shouldReceive('onDeviceVoltageEvent')
            ->withArgs(
                static function ($event) {
                    /** @var DeviceVoltageEvent $event */
                    return is_object($event)
                        && ($event instanceof DeviceVoltageEvent);
                }
            )->andReturnUsing(
                function ($event) use ($deviceVoltageConsumerSimulator) {
                    foreach ($event->getTrackerHistoryIDs() as $trackerHistoryID) {
                        $eventMessage = new DeviceVoltageQueueMessage($event->getDevice(), $trackerHistoryID);
                        $message = new AMQPMessage($eventMessage);
                        $deviceVoltageConsumerSimulator->execute($message);
                    }
                }
            );
        $this->getKernel()->getContainer()->set(DeviceListener::class, $listenerMock);
    }

    /**
     * @When I want handle sensor event
     */
    public function iWantHandleSensorEvent()
    {
        $consumer = \Mockery::mock(
            DeviceSensorConsumer::class,
            [
                $this->getEntityManager(),
                new EventDispatcher($this->getContainer()->get('event_dispatcher'), new TokenStorage()),
                \Mockery::mock($this->getContainer()->get('logger')),
                'test'
            ]
        )->makePartial();
        $trackerHistorySensors = $this->getEntityManager()->getRepository(TrackerHistorySensor::class)->findBy(
            [],
            ['createdAt' => 'DESC']
        );

        foreach ($trackerHistorySensors as $trackerHistorySensor) {
            $messageStub = \Mockery::mock(AMQPMessage::class);
            $messageStub
                ->shouldReceive('getBody')
                ->andReturn(
                    json_encode(
                        [
                            'device_id' => $trackerHistorySensor->getDevice()->getId(),
                            'tracker_history_sensor_id' => $trackerHistorySensor->getId()
                        ]
                    )
                );

            $consumer->execute($messageStub);
        }
    }

    /**
     * @When I want handle check towing event
     */
    public function iWantHandleCheckTowingEvent()
    {
        /** @var AreaService $areaService */
        $deviceTowingConsumerSimulator = \Mockery::mock(
            DeviceTowingConsumer::class,
            [
                $this->getEntityManager(),
                new EventDispatcher($this->getContainer()->get('event_dispatcher'), new TokenStorage()),
                \Mockery::mock($this->getContainer()->get('logger')),
                'test'
            ]
        )->makePartial();
        $listenerMock = $this->getDeviceListenerMockByEvent('onDeviceTowingEvent');
        $listenerMock->shouldReceive('onDeviceTowingEvent')
            ->withArgs(
                static function ($event) {
                    /** @var DeviceTowingEvent $event */
                    return is_object($event)
                        && ($event instanceof DeviceTowingEvent);
                }
            )->andReturnUsing(
                function ($event) use ($deviceTowingConsumerSimulator) {
                    foreach ($event->getTrackerHistoryIDs() as $trackerHistoryID) {
                        $eventMessage = new DeviceTowingQueueMessage($event->getDevice(), $trackerHistoryID);
                        $message = new AMQPMessage($eventMessage);
                        $deviceTowingConsumerSimulator->execute($message);
                    }
                }
            );
        $this->getKernel()->getContainer()->set(DeviceListener::class, $listenerMock);
    }

    /**
     * @When I want handle check panic button event from :source
     */
    public function iWantHandleCheckPanicButtonEvent($source)
    {
        /** @var AreaService $areaService */
        $devicePanicButtonConsumer = \Mockery::mock(
            PanicButtonConsumer::class,
            [
                $this->getEntityManager(),
                new EventDispatcher($this->getContainer()->get('event_dispatcher'), new TokenStorage()),
                \Mockery::mock($this->getContainer()->get('logger'))
            ]
        )->makePartial();

        $listenerMock = $this->getDeviceListenerMockByEvent('onDevicePanicButtonEvent');
        $listenerMock->shouldReceive('onDevicePanicButtonEvent')
            ->withArgs(
                static function ($event) {
                    /** @var DevicePanicButtonEvent $event */
                    return is_object($event)
                        && ($event instanceof DevicePanicButtonEvent);
                }
            )->andReturnUsing(
                function ($event) use ($devicePanicButtonConsumer, $source) {
                    switch ($event->getSource()) {
                        case DevicePanicButtonEvent::SOURCE_DEVICE:
                            foreach ($event->getTrackerHistoryData()['data'] as $trackerHistoryID) {
                                $eventMessage = new PanicButtonQueueMessage($event->getDevice(), $trackerHistoryID['th']);
                                $message = new AMQPMessage($eventMessage);
                                $devicePanicButtonConsumer->execute($message);
                            }
                            break;
                        case DevicePanicButtonEvent::SOURCE_MOBILE:
                            foreach ($event->getTrackerHistoryData() as $trackerHistoryID) {
                                $eventMessage = new PanicButtonQueueMessage($event->getDevice(), $trackerHistoryID);
                                $message = new AMQPMessage($eventMessage);
                                $devicePanicButtonConsumer->execute($message);
                            }
                            break;
                    }
                }
            );

        $this->getKernel()->getContainer()->set(DeviceListener::class, $listenerMock);
    }

    /**
     * @When I want handle overSpeeding event
     */
    public function iWantHandleOverSpeedingEvent()
    {
        $overSpeedingConsumerSimulator = \Mockery::mock(
            DeviceOverSpeedingConsumer::class,
            [
                $this->getEntityManager(),
                new EventDispatcher($this->getContainer()->get('event_dispatcher'), new TokenStorage()),
                $this->logger,
                $this->slaveEntityManager,
                'test'
            ]
        )->makePartial();
        $listenerMock = $this->getDeviceListenerMockByEvent('onDeviceOverSpeedingEvent');
        $listenerMock->shouldReceive('onDeviceOverSpeedingEvent')
            ->withArgs(
                static function ($event) {
                    return is_object($event) && ($event instanceof OverSpeedingEvent);
                }
            )->andReturnUsing(
                function ($event) use ($overSpeedingConsumerSimulator) {
                    foreach ($event->getTrackerHistoryIDs() as $trackerHistoryID) {
                        $eventMessage = new DeviceOverSpeedingQueueMessage($event->getDevice(), $trackerHistoryID);
                        $message = new AMQPMessage($eventMessage);
                        $overSpeedingConsumerSimulator->execute($message);
                    }
                }
            );
        $this->getKernel()->getContainer()->set(DeviceListener::class, $listenerMock);
    }

    /**
     * @When I want to handle engineOnTime event
     */
    public function iWantToHandleEngineOnTimeEvent()
    {
        $engineOnTimeConsumerSimulator = \Mockery::mock(EngineOnTimeConsumer::class, [$this->getEntityManager()])
            ->makePartial();
        $deviceListenerMock = $this->getDeviceListenerMockByEvent('onDeviceEngineOnTimeEvent');
        $deviceListenerMock->shouldReceive('onDeviceEngineOnTimeEvent')
            ->withArgs(
                static function ($event) {
                    return is_object($event) && ($event instanceof DeviceEngineOnTimeEvent);
                }
            )->andReturnUsing(
                function ($event) use ($engineOnTimeConsumerSimulator) {
                    $eventMessage = new EngineOnTimeQueueMessage($event->getDevice(), $event->getTrackerHistoryIDs());
                    $message = new AMQPMessage($eventMessage);
                    $engineOnTimeConsumerSimulator->execute($message);
                }
            );
        $this->getKernel()->getContainer()->set(DeviceListener::class, $deviceListenerMock);
    }

    /**
     * @When I want to set vehicle driver by user email :email and date :date
     * @param $email
     * @param $date
     * @throws \Exception
     */
    public function iWantToSetVehicleDriverByUserEmailAndDate($email, $date)
    {
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            throw new \Exception("User with email: $email is not found");
        }

        $this->fillData['startDate'] = $date;
        $this->post(
            '/api/vehicles/' . $this->vehicleData->id . '/set-driver/' . $user->getId(),
            $this->fillData
        );
    }
}
