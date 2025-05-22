<?php

namespace App\Service\Tracker;

use App\Entity\Client;
use App\Entity\Device;
use App\Entity\DeviceInstallation;
use App\Entity\DeviceModel;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerPayload;
use App\Entity\Tracker\Teltonika\TrackerSimulatorTrack;
use App\Entity\Tracker\Teltonika\TrackerSimulatorTrackPayload;
use App\Entity\Vehicle;
use App\Fixtures\Users\InitDemoUsersFixtures;
use App\Service\Tracker\Interfaces\SimulatorTrackerInterface;
use App\Service\Tracker\Parser\Teltonika\Model\Data;
use App\Service\Tracker\Parser\Teltonika\TcpDecoder;
use App\Service\Tracker\Parser\Teltonika\TcpEncoder;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TeltonikaSimulatorTrackerService extends SimulatorTrackerService implements SimulatorTrackerInterface
{
    private $devicesOffsetOnTrackTs;
    private $simulatorBaseImei;
    private $simulatorDevicesCount;

    /**
     * TeltonikaSimulatorTrackerService constructor.
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param TranslatorInterface $translator
     * @param int $simulatorBaseImei
     * @param int $simulatorDevicesCount
     * @param int $devicesOffsetOnTrackTs
     */
    public function __construct(
        EntityManager $em,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        int $simulatorBaseImei,
        int $simulatorDevicesCount,
        int $devicesOffsetOnTrackTs
    )
    {
        $this->em = $em;
        $this->simulatorBaseImei = $simulatorBaseImei;
        $this->simulatorDevicesCount = $simulatorDevicesCount;
        $this->devicesOffsetOnTrackTs = $devicesOffsetOnTrackTs;
    }

    /**
     * @param $data
     * @param TrackerPayload $trackerPayload
     * @param Device $device
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveRecords(
        $data,
        TrackerPayload $trackerPayload,
        Device $device
    ): array
    {
        $now = new \DateTime();
        $trackerHistoryIDs = [];
        $nowTs = $now->getTimestamp();
        $startOfDayTs = (clone $now)->setTime(0, 0, 0)->getTimestamp();
        $trackNumber = $this->getTrackNumber($device->getImei());
        $track = $this->em->getRepository(TrackerSimulatorTrack::class)
            ->findOneBy(['number' => $trackNumber]);

        if ($track) {
            $devicePosition = $this->getDevicePosition($device->getImei());
            $trackOffsetTs = $devicePosition * $this->devicesOffsetOnTrackTs;
            $iterationsLimit = floor((60 * 60 * 24) / ($track->getTrackDuration() + $trackOffsetTs)) - 1;
            $iteration = floor(($nowTs - $startOfDayTs) / $track->getTrackDuration());

            if ($iteration <= $iterationsLimit) {
                $secForOnePoint = $track->getTrackDuration() / $track->getPointsCount();
                $iterationStartedAt = (clone $now)->setTimestamp(
                    $startOfDayTs + $trackOffsetTs + ($iteration * $track->getTrackDuration())
                );
                $iterationFinishedAt = (clone $now)->setTimestamp(
                    $iterationStartedAt->getTimestamp() + $trackOffsetTs + $track->getTrackDuration()
                );
                $savedIterationPointsInDbCount = $this->em->getRepository(TrackerHistory::class)
                    ->getCoordinatesCount($device->getImei(), $iterationStartedAt, $iterationFinishedAt);

                /** @var Data $record */
                foreach ($data as $key => $record) {
                    $currentPositionStartedAt = (clone $now)->setTimestamp(
                        $iterationStartedAt->getTimestamp() + ($savedIterationPointsInDbCount * $secForOnePoint)
                    );
                    $pointTsAt = (clone $now)->setTimestamp(
                        $currentPositionStartedAt->getTimestamp() + (($key + 1) * $secForOnePoint)
                    );
                    $dateFromRecord = $record->getDateTime();
                    $updatedRecordDate = $dateFromRecord->setTimestamp($pointTsAt->getTimestamp());

                    $recordExists = $this->em->getRepository(TrackerHistory::class)->recordExistsForDevice(
                        $device,
                        $updatedRecordDate
                    );

                    $record->setDateTime($updatedRecordDate);

                    if (!$recordExists) {
                        $trackerHistory = $this->saveHistoryFromRecord($record, $trackerPayload, $device);
                        $trackerHistoryIDs[] = $trackerHistory->getId();
                        $this->saveSensorDataFromRecord(
                            $record,
                            $trackerPayload,
                            $trackerHistory,
                            $device
                        );

                        $this->em->flush();
                    }
                }
            }
        }

        return $trackerHistoryIDs;
    }

    /**
     * @param $imei
     * @return int
     */
    public function getTrackNumber($imei): int
    {
        $tracksCount = self::getCountOfTracks();
        $deviceNumber = $this->getDeviceNumber($imei);
        $dayOfYear = date('z') + 1;
        $deviceGroup = ceil($deviceNumber / $tracksCount);
        $trackNumber = (($deviceGroup + $deviceNumber + $dayOfYear) % $tracksCount) + 1;

        return $trackNumber;
    }

    /**
     * @param $imei
     * @return int
     */
    public function getDevicePosition($imei): int
    {
        $tracksCount = self::getCountOfTracks();
        $deviceNumber = $this->getDeviceNumber($imei);
        $deviceGroup = ceil($deviceNumber / $tracksCount);
        $firstDevicePositionInGroup = (($deviceGroup - 1) * $tracksCount);
        $devicePositionInGroup = ($deviceNumber - 1) - $firstDevicePositionInGroup;

        return $devicePositionInGroup;
    }

    /**
     * @param $imei
     * @return int
     */
    private function getDeviceNumber($imei): int
    {
        return ($imei - $this->simulatorBaseImei) + 1;
    }

    /**
     * @return int
     */
    public static function getCountOfTracks(): int
    {
        $files = new \FilesystemIterator(__DIR__ . '/../../../../tracker/teltonika/simulator/tracks',
            \FilesystemIterator::SKIP_DOTS);
        $filter = new \CallbackFilterIterator($files, function ($cur, $key, $iter) {
            return $cur->isFile();
        });

        return iterator_count($filter);
    }

    /**
     * @param bool $withDeviceInstallation
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function generateDevices($withDeviceInstallation = true): void
    {
        $client = $this->em->getRepository(Client::class)->findOneBy([
            'name' => InitDemoUsersFixtures::CLIENT_NAME_ACME
        ]);

        for ($currentImei = $this->simulatorBaseImei;
             $currentImei < $this->simulatorBaseImei + $this->simulatorDevicesCount;
             $currentImei++
        ) {
            $baseDevice = [
                'imei' => $currentImei,
                'model' => DeviceModel::MODELS['Teltonika'][0]['name']
            ];

            $deviceWithImei = $this->em->getRepository(Device::class)->count(['imei' => $currentImei]);

            if ($deviceWithImei == 0) {
                $deviceEntity = new Device($baseDevice);
                $deviceEntity->setTeam($client->getTeam());
                $deviceModel = $this->em->getRepository(DeviceModel::class)->findOneBy([
                    'name' => DeviceModel::MODELS['Teltonika'][0]['name']
                ]);
                $deviceEntity->setModel($deviceModel);
                $this->em->persist($deviceEntity);
                $devices[] = $deviceEntity;
            }

        };

        if ($withDeviceInstallation) {
            $vehicles = $this->em->getRepository(Vehicle::class)->getVehiclesWhichNotExistInDeviceInstallation();
            foreach ($vehicles as $key => $vehicle) {
                if (isset($devices[$key])) {
                    $deviceInstallation = new DeviceInstallation([
                        'vehicle' => $vehicle,
                        'device' => $devices[$key],
                        'installDate' => new \DateTime()
                    ]);

                    $this->em->persist($deviceInstallation);
                    $devices[$key]->install($deviceInstallation);
                }
            }
        }

        $this->em->flush();
    }

    /**
     * @param $imei
     * @param $dateFrom
     * @param $dateTo
     * @param $trackName
     * @param $location
     * @return TrackerSimulatorTrack
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function generateTrackPayloadsByImei(
        $imei,
        $dateFrom,
        $dateTo,
        $trackName = null,
        $location = null
    ): TrackerSimulatorTrack
    {
        $trackPayloads = [];
        $originalPayloads = $this->getTrackPayloadsByImei($imei, $dateFrom, $dateTo);
        $coordinates = $this->getCoordinatesByImei($imei, $dateFrom, $dateTo);

        $track = new TrackerSimulatorTrack();
        $track->setName($trackName);
        $track->setLocation($location);

        if ($coordinates) {
            $ts1 = array_shift($coordinates);
            $ts2 = end($coordinates);
            $duration = $ts2['ts']->getTimestamp() - $ts1['ts']->getTimestamp();

            $track->setTrackDuration($duration);
            $track->setStartedAt($ts1['ts']);
            $track->setFinishedAt($ts2['ts']);
        }

        $track->setPointsCount(count($coordinates));
        $this->em->persist($track);

        foreach ($originalPayloads as $payload) {
            $trackPayload = new TrackerSimulatorTrackPayload();
            $trackPayload->setSimulatorTrack($track);
            $trackPayload->setPayload($payload['payload']);
            $this->em->persist($trackPayload);

            $trackPayloads[] = $trackPayload;
        }

        $trackPayloads = new ArrayCollection($trackPayloads);
        $track->setPayloads($trackPayloads);

        $this->em->flush();

        return $track;
    }

    /**
     * @param $imei
     * @param $dateFrom
     * @param $dateTo
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function generateTracksByImei(
        $imei,
        $dateFrom,
        $dateTo
    ): array
    {
        $tracks = [];
        $startedCoordinate = null;
        $latDiff = 0.00007;
        $lngDiff = 0.0004;
        $durationTillNextPoint = 60 * 30;
        $durationToFinishCheck = 60 * 60 * 2;
        $coordinates = $this->getCoordinatesByImei($imei, $dateFrom, $dateTo);

        foreach ($coordinates as $key => $coordinate) {
            if ($startedCoordinate) {
                $tsCurrentCoordinate = $coordinate['ts']->getTimestamp();
                $tsStartedCoordinate = $startedCoordinate['ts']->getTimestamp();

                if (($tsCurrentCoordinate - $tsStartedCoordinate) < $durationTillNextPoint) {
                    continue;
                }

                if (($tsCurrentCoordinate - $tsStartedCoordinate) > $durationToFinishCheck) {
                    $startedCoordinate = null;
                    continue;
                }

                if ($coordinate['lat'] != $startedCoordinate['lat']
                    && $coordinate['lng'] != $startedCoordinate['lng']
                    && abs($coordinate['lat'] - $startedCoordinate['lat']) <= $latDiff
                    && abs($coordinate['lng'] - $startedCoordinate['lng']) <= $lngDiff
                ) {
                    $tracks[] = $this->generateTrackPayloadsByImei(
                        $imei, $tsStartedCoordinate, $tsCurrentCoordinate
                    );
                    $startedCoordinate = null;
                }
            } else {
                $startedCoordinate = $coordinate;
            }
        }

        return (new ArrayCollection($tracks))->map(
            function (TrackerSimulatorTrack $track) {
                return $track->toArray();
            }
        )->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getPayloadWithNewDateTime(
        string $payload,
        string $modelName,
        \DateTimeInterface $createdAt
    ): array {
        $decoder = new TcpDecoder();

        return $decoder->encodePayloadWithNewDateTime($payload, $modelName, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getAuthPayload(string $imei): string
    {
        $decoder = new TcpEncoder();

        return $decoder->convertImeiToPayload($imei);
    }

    /**
     * @param $imei
     * @param $dateFrom
     * @param $dateTo
     * @return mixed
     * @throws \Exception
     */
    public function getCoordinatesByImei($imei, $dateFrom, $dateTo): array
    {
        $dateFrom = $dateFrom ? Carbon::createFromTimestamp($dateFrom) : Carbon::now();
        $dateTo = $dateTo ? Carbon::createFromTimestamp($dateTo) : (new Carbon())->subHours(24);
        $device = $this->em->getRepository(Device::class)->getDeviceByImei($imei);

        return $device
            ? $this->em->getRepository(TrackerHistory::class)->getCoordinatesByDevice($device, $dateFrom, $dateTo)
            : [];
    }
}