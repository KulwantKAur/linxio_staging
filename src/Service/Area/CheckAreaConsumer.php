<?php

namespace App\Service\Area;

use App\Command\Traits\CommandLoggerTrait;
use App\Entity\Area;
use App\Entity\AreaHistory;
use App\Entity\Device;
use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Team;
use App\EntityManager\SlaveEntityManager;
use App\Service\Notification\ScopeService;
use App\Util\ExceptionHelper;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use Psr\Log\LoggerInterface;

class CheckAreaConsumer implements ConsumerInterface
{
    use CommandLoggerTrait;

    private $em;
    private $notificationDispatcher;
    private $logger;
    private $slaveEntityManager;
    private $scopeService;

    public const QUEUES_NUMBER = 4; // should be equal to number of queues in `config/rabbitmq.yaml`
    public const ROUTING_KEY_PREFIX = 'areas_check_device_'; // should be equal to `routing_keys` of queues

    /**
     * @param array $thObjectsArray
     * @return array|null
     */
    private function formatTHObjectsArray(array $thObjectsArray): ?array
    {
        return array_map(function (\stdClass $thObject) {
            return [
                'ts' => Carbon::parse($thObject->tsISO8601)->toDateTimeString(), // @todo format
                'lat' => $thObject->lat,
                'lng' => $thObject->lng,
                'speed' => $thObject->speed,
            ];
        }, $thObjectsArray);
    }

    public function __construct(
        EntityManager $em,
        NotificationEventDispatcher $notificationDispatcher,
        LoggerInterface $logger,
        SlaveEntityManager $slaveEntityManager,
        ScopeService $scopeService
    ) {
        $this->em = $em;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->logger = $logger;
        $this->slaveEntityManager = $slaveEntityManager;
        $this->scopeService = $scopeService;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     */
    public function execute(AMQPMessage $msg)
    {
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $message = json_decode($msg->getBody());

        if (!$message) {
            return;
        }

        $deviceId = $message->device_id;
        $trackerHistoriesArray = property_exists($message, 'tracker_history_data')
            ? $this->formatTHObjectsArray($message->tracker_history_data)
            : null;
        /** @var Device $device */
        $device = $this->em->getRepository(Device::class)->find($deviceId);

        if (!$device || !$device->getVehicle()) {
            return;
        }

        $area = $this->slaveEntityManager->getRepository(Area::class)->count([
            'team' => $device->getTeam(),
            'status' => Area::STATUS_ACTIVE
        ]);

        if (!$area) {
            return;
        }

        try {
            foreach ($trackerHistoriesArray as $item) {
                if (!isset($item['lat']) || !isset($item['lng'])) {
                    continue;
                }
                $timestamp = new \DateTime($item['ts']);
                $context = [
                    EventLog::LAT => $item['lat'],
                    EventLog::LNG => $item['lng']
                ];

                $areasPointInNow = $this->em->getRepository(Area::class)->findByPoint(
                    $item['lng'] . ' ' . $item['lat'],
                    $device->getTeam(),
                    ['id']
                );
                $areasPointInNow = array_column($areasPointInNow, 'id');
                foreach ($areasPointInNow as $areaId) {
                    //check if exists history about entering into Area
                    $arrivedAreaId = $this->em->getRepository(AreaHistory::class)
                        ->findAreaHistoryByVehicleAndDate($device->getVehicle(), $item['ts'], $areaId);

                    //if vehicle has't been in this area at this time - create new AreaHistory, if have been - nothing to do
                    if (!$arrivedAreaId) {
                        $area = $this->em->getRepository(Area::class)->find($areaId);
                        $areaHistory = new AreaHistory(
                            [
                                'area' => $area,
                                'vehicle' => $device->getVehicle(),
                                'driverArrived' => $device->getVehicle()->getDriver(),
                                'arrived' => $timestamp
                            ]
                        );
                        $this->em->persist($areaHistory);
                        $this->em->flush();

                        $ntfs = $this
                            ->checkNotifications(Event::VEHICLE_GEOFENCE_ENTER, $device->getTeam(), $timestamp);
                        foreach ($ntfs as $ntf) {
                            if (!$this->scopeService->filterNotifications([$ntf], $areaHistory, $context)) {
                                continue;
                            }
                            $context['notificationId'] = $ntf->getId();
                            $context['areas'] = [$areaHistory->toArray(AreaHistory::VEHICLE_DISPLAY_VALUES)];
                            $this->notificationDispatcher
                                ->dispatch(Event::VEHICLE_GEOFENCE_ENTER, $areaHistory, $timestamp, $context);
                        }
                    }
                }

                //set 'departed date' for areas in which point not consist now or update which left already
                $arrivedAreaHistory = $this->em->getRepository(AreaHistory::class)
                    ->findArrivedAreaHistory($device->getVehicle(), $item['ts']);

                foreach ($arrivedAreaHistory as $areaHistory) {
                    if (!in_array($areaHistory->getArea()->getId(), $areasPointInNow)) {
                        $areaHistory->setDeparted($timestamp);
                        $this->em->flush();

                        $ntfs = $this
                            ->checkNotifications(Event::VEHICLE_GEOFENCE_LEAVE, $device->getTeam(), $timestamp);
                        foreach ($ntfs as $ntf) {
                            if (!$this->scopeService->filterNotifications([$ntf], $areaHistory, $context)) {
                                continue;
                            }
                            $context['notificationId'] = $ntf->getId();
                            $context['areas'] = [$areaHistory->toArray(AreaHistory::VEHICLE_DISPLAY_VALUES)];
                            $this->notificationDispatcher
                                ->dispatch(Event::VEHICLE_GEOFENCE_LEAVE, $areaHistory, $timestamp, $context);
                        }
                    }
                }
                $this->em->flush();
            }

            $this->em->clear();
//            $this->slaveEntityManager->clear();
        } catch (\Throwable $e) {
            if (!$this->em->isOpen()) {
                $this->em = $this->em::create($this->em->getConnection(), $this->em->getConfiguration());
            }
            $this->logger->error(ExceptionHelper::convertToJson($e));
            $this->logException($e);
        }
    }

    public function checkNotifications(string $eventName, Team $team, $ts)
    {
        /** @var Event $event */
        $event = $this->em->getRepository(Event::class)->getEventByName($eventName, Event::TYPE_USER);

        return $this->em->getRepository(Notification::class)->getNotificationsByListenerTeam($event, $team, $ts);
    }
}
