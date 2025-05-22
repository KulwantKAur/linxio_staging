<?php

namespace App\Tests\Behat\Context\Traits;

use App\Entity\Area;
use App\Entity\AreaHistory;
use App\Entity\Client;
use App\Entity\DeviceModel;
use App\Entity\Team;
use App\EventListener\Area\AreaListener;
use App\Events\Area\CheckAreaEvent;
use App\Service\Area\AreaService;
use App\Service\Area\CheckAreaConsumer;
use App\Service\Area\CheckAreaQueueMessage;
use App\Service\DigitalForm\DigitalFormService;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Notification\EventDispatcher;
use App\Service\Setting\SettingService;
use App\Util\GeoHelper;
use DateTime;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

trait AreaTrait
{
    protected $areaData;
    protected $vehicleGroup = [];
    protected $client = [];

    /**
     * @When I want to create area and save id
     */
    public function iWantCreateArea()
    {
        $this->post('/api/areas', $this->fillData);
        $this->areaData = json_decode($this->getResponse()->getContent());
    }

    /**
     * @When I want to edit area by saved id
     */
    public function iWantEditAreaBySavedId()
    {
        $this->patch('/api/areas/' . $this->areaData->id, $this->fillData);
    }

    /**
     * @When I want to delete area by saved id
     */
    public function iWantDeleteAreaBySavedId()
    {
        $this->delete('/api/areas/' . $this->areaData->id);
    }

    /**
     * @When I want get area list
     */
    public function iWantGetAreaList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/areas?' . $params);
    }

    /**
     * @When I want get area by saved id
     */
    public function iWantGetAreaBySavedId()
    {
        $this->get('/api/areas/' . $this->areaData->id);
    }

    /**
     * @When I want fill area test coordinates :first :second
     */
    public function iWantFillAreaTestCoordinates($first, $second)
    {
        $this->fillData['coordinates'][] = ['lat' => $first, 'lng' => $second];
    }

    /**
     * @When I want check point in areas :first :second
     */
    public function iWantCheckPointInAreas($first, $second)
    {
        $this->fillData['point'] = [$first, $second];
        $this->post('/api/areas/check-point', $this->fillData);
    }

    /**
     * @When I want fill area ids with saved id
     */
    public function iWantFillAreaIdsSavedId()
    {
        $this->fillData['areaIds'][] = $this->areaData->id;
    }

    /**
     * @When I want fill areas id with saved id
     */
    public function iWantFillAreasIdSavedId()
    {
        $this->fillData['areaIds'] = $this->areaData->id;
    }

    /**
     * @When I want fill areas id with empty array
     */
    public function iWantFillAreasIdEmptyArray()
    {
        $this->fillData['areaIds'] = [];
    }


    /**
     * @When I want handle check area event
     */
    public function iWantHandleAreaEvent()
    {
        $formService = \Mockery::mock(DigitalFormService::class)->makePartial();
        $settingService = \Mockery::mock(SettingService::class, [
            $this->getEntityManager(),
            $this->getKernel()->getContainer()->get('translator'),
            $formService
        ])->makePartial();

        /** @var AreaService $areaService */
        $areaConsumerSimulator = \Mockery::mock(
            CheckAreaConsumer::class,
            [
                $this->getEntityManager(),
                new EventDispatcher($this->getContainer()->get('event_dispatcher'), new TokenStorage()),
                $settingService,
                $this->logger,
                $this->slaveEntityManager
            ]
        )->makePartial();

        $entityHistoryService = \Mockery::mock(
            EntityHistoryService::class,
            [new TokenStorage(), $this->getEntityManager()]
        )
            ->makePartial();


        $areaProducer = \Mockery::mock($this->getContainer()->get('old_sound_rabbit_mq.areas_producer'));

        $listenerMock = \Mockery::mock(
            sprintf('%s[processingCheckAreaEvent]', AreaListener::class),
            [
                $this->getEntityManager(),
                $entityHistoryService,
                $areaProducer
            ]
        )->makePartial();

        $listenerMock->shouldReceive('processingCheckAreaEvent')
            ->withArgs(
                static function ($event) {
                    /** @var CheckAreaEvent $event */
                    return is_object($event)
                        && ($event instanceof CheckAreaEvent);
                }
            )->andReturnUsing(
                function ($event) use ($areaConsumerSimulator) {
                    $eventMessage = new CheckAreaQueueMessage($event->getDevice(), $event->getData());
                    $message = new AMQPMessage($eventMessage);
                    $areaConsumerSimulator->execute($message);
                }
            );

        $this->getKernel()->getContainer()->set(AreaListener::class, $listenerMock);
    }

    /**
     * @When I want test distance between two coordinates :latFrom :lngFrom :latTo :lngTo
     */
    public function iWantTestDistanceBetweenTwoCoordinates($latFrom, $lngFrom, $latTo, $lngTo)
    {
        $distance = GeoHelper::distanceBetweenTwoCoordinates($latFrom, $lngFrom, $latTo, $lngTo);
        $this->setResponse(json_encode(['distance' => $distance]));
    }

    /**
     * @When I want test point in range for two coordinates :latFrom :lngFrom :latTo :lngTo
     */
    public function iWantTestPointInRangeForTwoCoordinates($latFrom, $lngFrom, $latTo, $lngTo)
    {
        $isInRange = GeoHelper::checkPointsInRange($latFrom, $lngFrom, $latTo, $lngTo);
        $this->setResponse(json_encode(['isInRange' => $isInRange]));
    }

    /**
     * @Given Current vehicle has been to :areaName from :from to :to
     */
    public function currentVehicleDepartedAndArrivedInGivenTime($areaName, $from, $to)
    {
        $vehicle = $this->getContainer()
            ->get('App\Service\Vehicle\VehicleService')
            ->getVehicleBy(['id' => $this->vehicleData->id]);

        $areaService = $this->getContainer()
            ->get('App\Service\Area\AreaService');

        $area = $areaService->getAreaBy(['name' => $areaName]);

        $data = [
            'area' => $area,
            'arrived' => new DateTime($from),
            'departed' => new DateTime($to),
            'vehicle' => $vehicle,
        ];
        $areaHistory = new AreaHistory($data);
        $areaService->saveAreaHistory($areaHistory);
    }

    /**
     * @When I want to get not visited areas using query :query
     */
    public function iWantToGetNotVisitedAreas($query)
    {
        $this->get('/api/reports/areas/not-visited' . $query);
    }

    /**
     * @When I want to get geofences summary using filter :query
     */
    public function iWantToGetGeofencesSummaryUsingFilter($query)
    {
        $this->get('/api/reports/areas/summary' . $query);
    }

    /**
     * @When I want to get visited areas
     */
    public function iWantToGetVisitedAreas()
    {
        $this->post('/api/reports/areas/visited', $this->fillData);
    }

    /**
     * @When I want to fill device model ID by ID of device model with name :model
     */
    public function iWantToFillDeviceModelIdByIdOfDeviceModelWithNames($model)
    {
        $model = $this->getEntityManager()->getRepository(DeviceModel::class)->findOneBy(['name' => $model]);
        $this->fillData['modelId'] = $model->getId();
    }

    /**
     * @When I want to fill team ID by team ID of client with name :name
     */
    public function iWantFillTeamidByTeamIdOfSavedClient($name)
    {
        /** @var \App\Entity\Client $client */
        $client = $this->getEntityManager()->getRepository(Client::class)->findOneBy(['name' => $name]);
        $this->fillData['teamId'] = $client->getTeam() instanceof Team ? $client->getTeam()->getId() : null;
    }

    /**
     * @When I want to get visited geofences grouped by geofence using query :query
     */
    public function iWantToGetVisitedGeofencesGroupedByGeofence($query)
    {
        $this->get('/api/reports/areas/grouped-visited' . $query);
    }

    /**
     * @When I want to get visits for given geofence :name using query :query
     */
    public function iWantToGetVisitsForGivenGeofence($name, $query)
    {
        $criteria = [
            'name' => $name,
            'team' => $this->authorizedUser->getTeam(),
        ];
        $area = $this->getEntityManager()->getRepository(Area::class)->findOneBy($criteria);

        $this->get('/api/reports/areas/' . $area->getId() . $query);
    }
}
