<?php

namespace App\Command;

use App\Command\Traits\CommandLoggerTrait;
use App\Command\Traits\DevicebleTrait;
use App\Command\Traits\ProcessableTrait;
use App\Command\Traits\RedisLockTrait;
use App\Command\Traits\TeamTrait;
use App\Entity\Device;
use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\Tracker\TrackerHistory;
use App\Service\Device\Consumer\TrackerHistoryConsumerTrait;
use App\Service\Device\DeviceOverSpeedingQueue\DeviceExceedingSpeedLimitConsumer;
use App\Service\MapService\MapServiceInterface;
use App\Service\MapService\MapServiceResolver;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Notification\ScopeService;
use App\Service\Redis\MemoryDbService;
use App\Service\Redis\Models\DeviceRedisModel;
use App\Util\DateHelper;
use App\Util\GeoHelper;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client as GuzzleHttpClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @see DeviceExceedingSpeedLimitConsumer
 * Part with reverseGeocode is in DeviceExceedingSpeedLimitConsumer
 */
#[AsCommand(name: 'app:device:exceed-speed-limit')]
class DeviceExceedingSpeedLimitCommand extends Command
{
    use ProcessableTrait, CommandLoggerTrait, TrackerHistoryConsumerTrait, DevicebleTrait, TeamTrait, RedisLockTrait;

    private const string TAG_EXCEEDING_SPEED_LIMIT = DeviceExceedingSpeedLimitConsumer::TAG_EXCEEDING_SPEED_LIMIT;
    private const int MAX_TTL = DeviceExceedingSpeedLimitConsumer::MAX_TTL;
    private const int IGNORE_STOPS = DeviceExceedingSpeedLimitConsumer::IGNORE_STOPS;
    private const int TOMTOM_QPS = DeviceExceedingSpeedLimitConsumer::TOMTOM_QPS;
    private const int DAYS_DIFF_VALID = DeviceExceedingSpeedLimitConsumer::DAYS_DIFF_VALID;
    private const int ANGLE_LIMIT = 10;
    private const int MAX_POINTS_IN_ONE_REQUEST = 5000;

    private int $requestCount = 0;
    protected array $cacheData = [];
    protected string $redisKey;

    private function buildPointsUrlPath(array $trackerHistories, TrackerHistory $prevTh): string
    {
        array_unshift($trackerHistories, $prevTh);
        $url = '&points=';
        array_map(function (TrackerHistory $trackerHistory) use (&$url) {
            $url .= $trackerHistory->getLng() . ',' . $trackerHistory->getLat() . ';';

            return $trackerHistory;
        }, $trackerHistories);

        return trim($url, ';');
    }

    private function buildPointsBodyPost(array $trackerHistories, TrackerHistory $prevTh): array
    {
        $prevThClone = clone $prevTh;
        $prevThClone->setTs($trackerHistories[0]?->getTs());
        array_unshift($trackerHistories, $prevThClone);
        $points = [];

        foreach ($trackerHistories as $trackerHistory) {
            $points[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [floatval($trackerHistory->getLng()),floatval($trackerHistory->getLat())]
                ],
                'properties' => [
                    'heading' => 0,
                    'timestamp' => $trackerHistory->getTs()->format('c') . 'Z',
                ]
            ];
        }

        return ['points' => $points];
    }

    private function buildTsUrlPath(array $trackerHistories, TrackerHistory $prevTh): string
    {
        $prevThClone = clone $prevTh;
        $prevThClone->setTs($trackerHistories[0]?->getTs());
        array_unshift($trackerHistories, $prevThClone);
        $url = '&timestamps=';
        array_map(function (TrackerHistory $trackerHistory) use (&$url) {
            $url .= urlencode($trackerHistory->getTs()->format('c')) . 'Z;';

            return $trackerHistory;
        }, $trackerHistories);

        return trim($url, ';');
    }

    private function preHandleRequest(): void
    {
        if ($this->requestCount > self::TOMTOM_QPS) {
            sleep(1);
            $this->requestCount = 0;
        }
    }

    private function postHandleRequest(): void
    {
        $this->requestCount++;
    }

    private function getRemoteSpeedLimitData(array $trackerHistoriesChunk, TrackerHistory $prevTh): array
    {
//        return $this->getRemoteSpeedLimitDataGet($trackerHistoriesChunk, $prevTh);
        return $this->getRemoteSpeedLimitDataPost($trackerHistoriesChunk, $prevTh);
    }

    private function getRemoteSpeedLimitDataGet(array $trackerHistoriesChunk, TrackerHistory $prevTh): array
    {
        $pointsUrlPart = $this->buildPointsUrlPath($trackerHistoriesChunk, $prevTh);
        $tsUrlPart = $this->buildTsUrlPath($trackerHistoriesChunk, $prevTh);
        $oldUrlFieldsPart = '&fields={route{properties{id,speedLimits{value,unit,type}}}}';
        $newUrlFieldsPart = '&fields={route{geometry{coordinates},properties{speedLimits{value}}}}';

        try {
            $this->preHandleRequest();
            $url = 'https://api.tomtom.com/snapToRoads/1?key=' . $this->tomtomKey . $pointsUrlPart . $tsUrlPart
                . $newUrlFieldsPart . '&vehicleType=PassengerCar';
            $response = (new GuzzleHttpClient())->get($url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ]
                ]
            );
            $body = json_decode($response->getBody()->getContents(), true);
            // @todo uncomment to test with fake body
            // $body = json_decode($this->getFakeBody(), true);
        } catch (\Throwable $e) {
            $this->logException($e, ['url' => $url]);
            throw $e;
        } finally {
            $this->postHandleRequest();
        }

        return $body['route'] ?? [];
    }

    private function getRemoteSpeedLimitDataPost(array $trackerHistoriesChunk, TrackerHistory $prevTh): array
    {
        $bodyData = $this->buildPointsBodyPost($trackerHistoriesChunk, $prevTh);
        $urlFieldsPart = '&fields={route{geometry{coordinates},properties{speedLimits{value}}}}';

        try {
            $this->preHandleRequest();
            $url = 'https://api.tomtom.com/snapToRoads/1?key=' . $this->tomtomKey . $urlFieldsPart .
                '&vehicleType=PassengerCar';
            $response = (new GuzzleHttpClient())->post($url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ],
                    'body' => json_encode($bodyData)
                ]
            );
            $body = json_decode($response->getBody()->getContents(), true);
            // @todo uncomment to test with fake body
            // $body = json_decode($this->getFakeBody(), true);
        } catch (\Throwable $e) {
            $this->logException($e, ['url' => $url]);
            throw $e;
        } finally {
            $this->postHandleRequest();
        }

        return $body['route'] ?? [];
    }

    private function updateLastDateForDevice(array $allDataByDevice, string $lastExceedingSpeedLimitDateKey): void
    {
        $lastThDate = isset(end($allDataByDevice)['th']) ? end($allDataByDevice)['th']->getTs() : null;

        if ($lastThDate) {
            $lastThDate = Carbon::parse(clone $lastThDate)->addSecond();
            $this->memoryDb->set($lastExceedingSpeedLimitDateKey, $lastThDate->getTimestamp());
        }
    }

    private function getFakeBody()
    {
        return '{
          "route": [
            {
              "geometry": {
                "coordinates": [
                  [
                    4.6108970046,
                    52.3756442964
                  ],
                  [
                    4.6109291911,
                    52.3757502437
                  ],
                  [
                    4.6110069752,
                    52.3759138584
                  ],
                  [
                    4.6110418439,
                    52.3759916425
                  ],
                  [
                    4.6110659838,
                    52.3760412633
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 70
                }
              }
            },
            {
              "geometry": {
                "coordinates": [
                  [
                    4.6130333841,
                    52.3786470294
                  ],
                  [
                    4.613083005,
                    52.3787033558
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 70
                }
              }
            },
            {
              "geometry": {
                "coordinates": [
                  [
                    4.613379389,
                    52.3790600896
                  ],
                  [
                    4.6134102345,
                    52.3790976405
                  ],
                  [
                    4.613442421,
                    52.3791351914
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 70
                }
              }
            },
            {
              "geometry": {
                "coordinates": [
                  [
                    4.613905102,
                    52.379758805
                  ],
                  [
                    4.6140727401,
                    52.380014956
                  ],
                  [
                    4.6141116321,
                    52.3800887167
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 40
                }
              }
            },
            {
              "geometry": {
                "coordinates": [
                  [
                    4.6141116321,
                    52.3800887167
                  ],
                  [
                    4.6141947806,
                    52.3802402616
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 70
                }
              }
            },
            {
              "geometry": {
                "coordinates": [
                  [
                    4.6141947806,
                    52.3802402616
                  ],
                  [
                    4.614276588,
                    52.3804025352
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 70
                }
              }
            },
            {
              "geometry": {
                "coordinates": [
                  [
                    4.614276588,
                    52.3804025352
                  ],
                  [
                    4.6143516898,
                    52.3805473745
                  ],
                  [
                    4.614405334,
                    52.3806841671
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 70
                }
              }
            },
            {
              "geometry": {
                "coordinates": [
                  [
                    4.6147486567,
                    52.3839658499
                  ],
                  [
                    4.6147486567,
                    52.3844526708
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 70
                }
              }
            },
            {
              "geometry": {
                "coordinates": [
                  [
                    4.6147486567,
                    52.3844526708
                  ],
                  [
                    4.6147486567,
                    52.3844915628
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 70
                }
              }
            },
            {
              "geometry": {
                "coordinates": [
                  [
                    4.6147486567,
                    52.3844915628
                  ],
                  [
                    4.6147486567,
                    52.3845277727
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 70
                }
              }
            },
            {
              "geometry": {
                "coordinates": [
                  [
                    4.6147486567,
                    52.3845277727
                  ],
                  [
                    4.6147473156,
                    52.3845908046
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 70
                }
              }
            },
            {
              "geometry": {
                "coordinates": [
                  [
                    4.6152073145,
                    52.3864281178
                  ],
                  [
                    4.6153508127,
                    52.3867271841
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 70
                }
              }
            },
            {
              "geometry": {
                "coordinates": [
                  [
                    4.6153508127,
                    52.3867271841
                  ],
                  [
                    4.6153923869,
                    52.3868143559
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 70
                }
              }
            },
            {
              "geometry": {
                "coordinates": [
                  [
                    4.6154902875,
                    52.3870182037
                  ],
                  [
                    4.6153494716,
                    52.3870450258
                  ],
                  [
                    4.6152475476,
                    52.3870638013
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 50
                }
              }
            },
            {
              "geometry": {
                "coordinates": [
                  [
                    4.6152475476,
                    52.3870638013
                  ],
                  [
                    4.6151416004,
                    52.3868599534
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 70
                }
              }
            },
            {
              "geometry": {
                "coordinates": [
                  [
                    4.6151416004,
                    52.3868599534
                  ],
                  [
                    4.6150960028,
                    52.3867727816
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 70
                }
              }
            },
            {
              "geometry": {
                "coordinates": [
                  [
                    4.6150960028,
                    52.3867727816
                  ],
                  [
                    4.6149685979,
                    52.3864898086
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 70
                }
              }
            },
            {
              "geometry": {
                "coordinates": [
                  [
                    4.6145635843,
                    52.3846109211
                  ],
                  [
                    4.6145609021,
                    52.384557277
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 70
                }
              }
            },
            {
              "geometry": {
                "coordinates": [
                  [
                    4.614559561,
                    52.3844768107
                  ],
                  [
                    4.6145743132,
                    52.3841388524
                  ],
                  [
                    4.6145863831,
                    52.3836319149
                  ],
                  [
                    4.6145904064,
                    52.3835259676
                  ],
                  [
                    4.6145904064,
                    52.383428067
                  ],
                  [
                    4.6145904064,
                    52.383402586
                  ]
                ]
              },
              "properties": {
                "speedLimits": {
                  "value": 70
                }
              }
            }
          ]
        }';
    }

    /**
     * @see DeviceExceedingSpeedLimitConsumer
     * @see DeviceExceedingSpeedLimitCommand:273
     */
    private function getSpeedLimitBatch(array $trackerHistories): array
    {
        $trackerHistoriesChunks = array_chunk($trackerHistories, self::MAX_POINTS_IN_ONE_REQUEST);
        $resultTotalData = [];

        foreach ($trackerHistoriesChunks as $key => $trackerHistoriesChunk) {
            $resultChunkData = [];
            $trackerHistory = $trackerHistoriesChunk[0];
            $prevTh = $trackerHistoriesChunk[$key - 1] ?? null;
            $prevTh = $prevTh ?: $this->em->getRepository(TrackerHistory::class)
                ->getPreviousTrackerHistory($trackerHistory, null, true);

            if (!$prevTh) {
                continue;
            }

            $speedLimitData = $this->getRemoteSpeedLimitData($trackerHistoriesChunk, $prevTh);

            foreach ($speedLimitData as $speedLimitDatum) {
                $speedLimitCoordinates = $speedLimitDatum['geometry']['coordinates'] ?? null;
                $speedLimitValue = $speedLimitDatum['properties']['speedLimits']['value'] ?? null;
                $speedLimitValue = $speedLimitValue ? (float) $speedLimitValue : null;

                if (!$speedLimitCoordinates || !$speedLimitValue) {
                    continue;
                }

                $resultDatum = [
                    'coordinatesStart' => reset($speedLimitCoordinates),
                    'coordinatesFinish' => end($speedLimitCoordinates),
                    'value' => $speedLimitValue,
                ];
                $resultChunkData[] = $resultDatum;
            }

            $resultTotalData = array_merge($resultTotalData, $resultChunkData);
        }

        return $resultTotalData;
    }

    private function getTHAndNtfDataBySLDate(
        Device             $device,
        \DateTimeInterface $lastExceedingSpeedLimitDate
    ): array {
        $allDataByDevice = [];
        $THsByDeviceQuery = $this->em->getRepository(TrackerHistory::class)->getTrackerRecordsByDeviceInRangeQuery(
            $device->getId(),
            $lastExceedingSpeedLimitDate,
            null,
            true,
            true
        );

        /** @var TrackerHistory $trackerHistory */
        foreach ($THsByDeviceQuery->toIterable() as $thKey => $trackerHistory) {
            if (DateHelper::getDiffInDaysNow($trackerHistory->getTs()) > self::DAYS_DIFF_VALID
                || !$this->isValidToTriggerEvent(
                    DateHelper::formatDate($trackerHistory->getTs()),
                    DateHelper::formatDate($trackerHistory->getCreatedAt()),
                )
            ) {
                $this->em->clear();
                continue;
            }

            $trackerData = $this->formatTHToArray($trackerHistory);
            $notifications = $this->em->getRepository(Notification::class)
                ->getNotificationsByListenerTeam($this->event, $device->getTeam(), $trackerHistory->getTs());

            if (!$notifications) {
                $this->em->clear();
                continue;
            }

            // getting a list of notifications for the entity by received device
            $notifications = $this->scopeService->filterNotifications(
                $notifications,
                $trackerHistory,
                [
                    EventLog::LAT => $trackerHistory->getLat(),
                    EventLog::LNG => $trackerHistory->getLng()
                ]
            );

            if (!$notifications) {
                $this->em->clear();
                continue;
            }

            $allDataByDevice[$thKey]['th'] = $trackerHistory;
            $allDataByDevice[$thKey]['thData'] = $trackerData;
            $allDataByDevice[$thKey]['ntf'] = $notifications;
        }

        return $allDataByDevice;
    }

    private function matchSpeedLimitsToAllCoordinates(array $allDataByDevice, array $speedLimitData): array
    {
        foreach ($allDataByDevice as $datumKey => $datumByDevice) {
            $trackerHistory = $datumByDevice['th'];
            $lat = $trackerHistory->getLat();
            $lng = $trackerHistory->getLng();
            $minDistance = null;
            $speedLimitValue = null;

            foreach ($speedLimitData as $speedLimitDatum) {
                list($lngStart, $latStart) = $speedLimitDatum['coordinatesStart'];
                list($lngFinish, $latFinish) = $speedLimitDatum['coordinatesFinish'];
                $distanceStart = GeoHelper::distanceBetweenTwoCoordinates($lat, $lng, $latStart, $lngStart);
                $distanceFinish = GeoHelper::distanceBetweenTwoCoordinates($lat, $lng, $latFinish, $lngFinish);
                $distance = min($distanceStart, $distanceFinish);
                $minDistance = $minDistance ? min($distance, $minDistance) : $distance;

                if ($minDistance == $distance) {
                    $speedLimitValue = $speedLimitDatum['value'] ?? null;
                }
            }

            $allDataByDevice[$datumKey]['speedLimit'] = $speedLimitValue;
        }

        return $allDataByDevice;
    }

    private function getCoordinatesWithOptimizationByAngle(array $allDataByDevice): ?array
    {
        $THsFiltered = [];
        $i = 0;
        $angleLimit = self::ANGLE_LIMIT;
        $THs = array_column($allDataByDevice, 'th');
        $THLast = end($THs);
        array_pop($THs);

        foreach ($THs as $TH) {
            $prevAngle = isset($THsFiltered[$i - 1]) ? $THsFiltered[$i - 1]->getAngle() : null;
            $currentAngle = $TH->getAngle();

            if ($currentAngle && !empty($THsFiltered)) {
                $prevAngleMin = ($prevAngle - $angleLimit) < 0 ? 360 - ($prevAngle - $angleLimit) : $prevAngle - $angleLimit;
                $prevAngleMax = $prevAngle + $angleLimit > 360 ? ($prevAngle + $angleLimit) - 360 : $prevAngle + $angleLimit;

                if (($currentAngle < $prevAngleMax && $currentAngle > $prevAngleMin)
                    || ($currentAngle < $prevAngle + $angleLimit && $currentAngle > $prevAngle - $angleLimit)
                ) {
                    continue;
                }
            }

            $THsFiltered[$i] = $TH;
            $i++;
        }

        $THsFiltered[$i] = $THLast;

        return $THsFiltered;
    }

    private function handleNtf(Notification $ntf, array $datumByDevice, string $status): void
    {
        $trackerHistory = $datumByDevice['th'];
        $trackerData = $datumByDevice['thData'];
        $speedLimit = $datumByDevice['speedLimit'];

        //trigger just by speed
        if (!$ntf->getTimeDurationParam() && !$ntf->getDistanceParam() && !$this->cacheData) {
            $this->cacheData = $this->memoryDb->getFromJson($this->redisKey) ?: [];
            $distance = $this->getDistanceForContext($trackerData);
            $duration = $this->getDurationForContext($trackerData);
            $this->triggerOverspeedingNtf($trackerHistory, $ntf, $speedLimit, $duration, $distance);

            return;
        }

        if ($ntf->getTimeDurationParam() && !$ntf->getDistanceParam() && $this->cacheData) {
            $distance = $this->getDistanceForContext($trackerData);
            $duration = $this->getDuration($ntf, $trackerData, $status);

            if ($duration) {
                $this->triggerOverspeedingNtf($trackerHistory, $ntf, $speedLimit, $duration, $distance);

                return;
            }
        }

        if ($ntf->getDistanceParam() && !$ntf->getTimeDurationParam() && $this->cacheData) {
            $distance = $this->getDistance($ntf, $trackerData, $status);
            $duration = $this->getDurationForContext($trackerData);

            if ($distance) {
                $this->triggerOverspeedingNtf($trackerHistory, $ntf, $speedLimit, $duration, $distance);

                return;
            }
        }

        if ($ntf->getDistanceParam() && $ntf->getTimeDurationParam() && $this->cacheData) {
            $distance = $this->getDistance($ntf, $trackerData, $status);
            $duration = $this->getDuration($ntf, $trackerData, $status);

            if ($distance && $duration) {
                $this->triggerOverspeedingNtf($trackerHistory, $ntf, $speedLimit, $duration, $distance);

                return;
            }
        }
    }

    private function handleTeamDevices(Team $team, OutputInterface $output)
    {
        $deviceIds = $this->em->getRepository(Device::class)->getDeviceIds(teamIds: [$team->getId()]);

        foreach ($deviceIds as $deviceId) {
            $output->writeln('Device id: ' . $deviceId);

            try {
                $device = $this->em->getRepository(Device::class)->getDeviceWithVehicle($deviceId);
                $vehicle = $device?->getVehicle();

                if (!$device || !$vehicle) {
                    $this->em->clear();
                    continue;
                }

                $lastExceedingSpeedLimitDateKey = DeviceRedisModel::getByName(
                    $device->getId(), DeviceRedisModel::DEVICE_EXCEEDING_SPEED_LIMIT_DATE
                );
                $lastExceedingSpeedLimitDate = $this->memoryDb->get($lastExceedingSpeedLimitDateKey)
                    ? Carbon::parse($this->memoryDb->get($lastExceedingSpeedLimitDateKey))
                    : (($device->getLastTrackerRecord()?->getTs()
                        && DateHelper::getDiffInDaysNow($device->getLastTrackerRecord()?->getTs())
                            <= self::DAYS_DIFF_VALID
                        )
                            ? Carbon::parse($device->getLastTrackerRecord()?->getTs())
                            : (new Carbon())->subHour()
                    ); // @todo change time?
                $lastExceedingSpeedLimitDate = max($device->getInstallDate(), $lastExceedingSpeedLimitDate);
                $allDataByDevice = $this->getTHAndNtfDataBySLDate($device, $lastExceedingSpeedLimitDate);

                if (empty($allDataByDevice)) {
                    continue;
                }

                $THsFiltered = (count($allDataByDevice) > 2)
                    ? $this->getCoordinatesWithOptimizationByAngle($allDataByDevice)
                    : array_column($allDataByDevice, 'th');
                $speedLimitData = $this->getSpeedLimitBatch($THsFiltered);
                $allDataByDevice = $this->matchSpeedLimitsToAllCoordinates($allDataByDevice, $speedLimitData);

                foreach ($allDataByDevice as $datumByDevice) {
                    try {
                        $trackerHistory = $datumByDevice['th'];
                        $trackerData = $datumByDevice['thData'];
                        $notifications = $datumByDevice['ntf'];
                        $speedLimit = $datumByDevice['speedLimit'];
                        $status = $this->getStatus($trackerData, self::TAG_EXCEEDING_SPEED_LIMIT);

                        /** @var Notification $ntf */
                        foreach ($notifications as $ntf) {
                            $this->redisKey = self::TAG_EXCEEDING_SPEED_LIMIT . '-' . 'eventId-' .
                                $this->event->getId() . 'deviceId-' . $device->getId() . 'ntfId-' . $ntf->getId();
                            $this->cacheData = $this->memoryDb->getFromJson($this->redisKey) ?: [];
                            $this->cacheData = $this->updateCache(
                                ['status' => $status], self::TAG_EXCEEDING_SPEED_LIMIT
                            );

                            if (is_null($speedLimit)
                                || ($speedLimit + $ntf->getThresholdParam()) > $trackerHistory->getSpeed()
                            ) {
                                if ($this->memoryDb->deleteItem($this->redisKey)) {
                                    $this->cacheData = [];
                                }

                                continue;
                            }

                            // add only if there is a trigger condition
                            if (!$this->cacheData) {
                                $this->memoryDb->setToJsonTtl(
                                    $this->redisKey,
                                    $this->setDataCache($ntf, $this->event, $device, $vehicle, $trackerData, $status),
                                    self::TAG_EXCEEDING_SPEED_LIMIT,
                                    self::MAX_TTL,
                                );

//                              $this->logger->info('Save cache', ['redisKey' => $this->redisKey]);
//                              continue;
                            }

                            $this->handleNtf($ntf, $datumByDevice, $status);
                        }
                    } catch (\Throwable $e) {
                        $this->logException($e, ['datumByDevice' => $datumByDevice]);
                    }
                }

                $this->em->flush();
                $this->em->clear();
                $this->updateLastDateForDevice($allDataByDevice, $lastExceedingSpeedLimitDateKey);
            } catch (\Throwable $e) {
                // @todo handle such error
                // Client error: `POST https://api.tomtom.com/snapToRoads/1?key=wq9gf4mGj2dyK5ccP19GBxkaZA6Iy1Au&fields=%7Broute%7Bgeometry%7Bcoordinates%7D,properties%7BspeedLimits%7Bvalue%7D%7D%7D%7D&vehicleType=PassengerCar` resulted in a `400 Bad Request` response:
                // {"detailedError":{"code":"INVALID_REQUEST","message":"Distance limit [100000.00 m] exceeded by point [0] about [9159102. (truncated...)
                $this->logException($e, ['deviceId' => $deviceId]);

                if (isset($allDataByDevice) && isset($lastExceedingSpeedLimitDateKey)) {
                    $this->updateLastDateForDevice($allDataByDevice, $lastExceedingSpeedLimitDateKey);
                }
            }
        }
    }

    protected function configure(): void
    {
        $this->setDescription('Process device exceeding speed limit');
        $this->updateConfigWithProcessOptions();
        $this->updateConfigWithDeviceOptions();
        $this->updateConfigWithTeamOptions();
    }

    public function __construct(
        private readonly EntityManager               $em,
        private readonly NotificationEventDispatcher $notificationDispatcher,
        private readonly LoggerInterface             $logger,
        private readonly MapServiceResolver          $mapServiceResolver,
        private readonly MemoryDbService             $memoryDb,
        private readonly ScopeService                $scopeService,
        private readonly ParameterBagInterface       $params,
        private readonly string                      $tomtomKey,
        private ?MapServiceInterface                 $mapService,
        private ?Event                               $event,
    ) {
        $this->mapService = $mapServiceResolver->getInstance();
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lock = $this->getLock($this->getProcessName($input));

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $teamIds = $this->getSlicedItemsByProcess(
            $this->em->getRepository(Team::class)->getTeamIds(),
            $input,
            $output
        );
        $progressBar = new ProgressBar($output, count($teamIds));
        $progressBar->start();
        $this->event = $this->em->getRepository(Event::class)->getEventByName(Event::EXCEEDING_SPEED_LIMIT);

        foreach ($teamIds as $teamId) {
            try {
                $team = $this->em->getRepository(Team::class)->find($teamId);
                $addons = $team->getSettingsByName(Setting::BILLABLE_ADDONS);

                if (!$addons
                    || !in_array(Setting::BILLABLE_ADDONS_SIGN_POST_SPEED_DATA, $addons->getValue())
                    || !in_array(Setting::BILLABLE_ADDONS_SNAP_TO_ROADS, $addons->getValue())
                ) {
                    $progressBar->advance();
                    continue;
                }

                $this->handleTeamDevices($team, $output);
                $progressBar->advance();
            } catch (\Throwable $e) {
                $this->logException($e, ['teamId' => $teamId]);
                continue;
            }
        }

        $progressBar->finish();
        $output->writeln(PHP_EOL . 'Device exceeding speed limit successfully processed!');
        $this->em->flush();
        $this->em->clear();

        return 0;
    }
}