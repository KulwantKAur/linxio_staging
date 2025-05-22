<?php

namespace App\Service\FuelCard;

use App\Entity\Device;
use App\Entity\DriverHistory;
use App\Entity\File;
use App\Entity\FuelCard\FuelCard;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Service\BaseService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\File\FileService;
use App\Service\File\LocalFileService;
use App\Service\FuelCard\Import\FileImport;
use App\Service\MapService\MapServiceResolver;
use App\Service\Setting\SettingService;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManagerInterface;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

class FuelCardService extends BaseService
{
    protected TransformedFinder $fuelCardFinder;
    protected $mapService;
    private $fuelCardRepo;
    private FileImport $fileImport;
    private LoggerInterface $logger;

    const ELASTIC_NESTED_FIELDS = [];
    const ELASTIC_SIMPLE_FIELDS = [
        'id' => 'id',
        'fuelCardNumber' => 'fuelCardNumber',
        'petrolStation' => 'petrolStation',
        'status' => 'status',
        'vehicleDepot' => 'vehicle.depot.id',
        'vehicleModel' => 'vehicle.model',
        'vehicleRegNo' => 'vehicle.regno',
        'vehicleDefaultLabel' => 'vehicle.defaultLabel',
        'vehicleGroups' => 'vehicle.groups.id',
        'vehicleFuelType' => 'vehicle.fuelType.id',
        'teamId' => 'teamId',
        'driver' => 'driver.fullName',
        'refueledFuelType' => 'refueledFuelType.id'
    ];
    const ELASTIC_RANGE_FIELDS = [
        'transactionDate' => 'transactionDate',
        'refueled' => 'refueled',
        'total' => 'total',
        'fuelPrice' => 'fuelPrice',
    ];

    private const BATCH_SIZE = 100;

    public function __construct(
        TransformedFinder $fuelCardFinder,
        private readonly TranslatorInterface $translator,
        private readonly FileService $fileService,
        private readonly EntityManagerInterface $em,
        MapServiceResolver $mapServiceResolver,
        private readonly SettingService $settingService,
        FileImport $fileImport,
        LoggerInterface $logger,
    ) {
        $this->fuelCardFinder = $fuelCardFinder;
        $this->fuelCardRepo = $em->getRepository(FuelCard::class);
        $this->mapService = $mapServiceResolver->getInstance();
        $this->fileImport = $fileImport;
        $this->logger = $logger;
    }

    public function parseFiles(array $data, User $user)
    {
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            if ($data['files']->get('files') ?? null) {
                foreach ($data['files']->get('files') as $file) {

                    /** @var File $fileEntity */
                    $fileEntity = $this->fileService->uploadFuelCardFile($file, $user);
                    $this->fileImport->import($fileEntity);
                }
            }
            $connection->commit();

            return \array_merge(
                ['file' => $fileEntity->toArray()],
                ['data' => $this->getById($fileEntity->getId())]
            );
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * @param UploadedFile $file
     * @return array|void
     */
    public function sftpUploadFile(UploadedFile $file, ?array $ownerTeamId = null)
    {
        try {
            /** @var File $fileEntity */
            $fileEntity = $this->fileService->uploadSFTPFuelFile($file);
            $this->fileImport->importChevron($fileEntity, true, $ownerTeamId);
            $this->updateByFileId($fileEntity->getId());

            return ['file' => $fileEntity->toArray()];
        } catch (\Throwable $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));

            return [
                'error' => $this->translator->trans('validation.errors.import.error_upload_file')
            ];
        }
    }

    /**
     * @param $id
     */
    public function deleteByFileId($id)
    {
        $this->fuelCardRepo->deleteUploadedFile($id);
    }

    /**
     * @param $id
     * @return FuelCard[]|array|object[]
     */
    public function getById($id)
    {
        return $this->fuelCardRepo->findBy(['file' => $id], ['id' => 'ASC']);
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function updateByFileId($id)
    {
        try {
            $counter = 0;
            $fuelCards = $this->fuelCardRepo->getDraftFilesRecords($id, true);
            
            foreach ($fuelCards as $fuelCard) {
                $comments = $fuelCard->getFuelCardTemporary()->getComments();
                
                if (! array_key_exists('error', $comments)) {
                    $fuelPrice = (($fuelCard->getRefueled() > 0) && ($fuelCard->getTotal() > 0))
                        ? round($fuelCard->getTotal() / $fuelCard->getRefueled(), 2)
                        : null;

                    $driver = ($fuelCard->isShowTime()) ? $this->findDriverByVehicle($fuelCard) : null;

                    $lastVehicleCoordinates = ($fuelCard->isShowTime())
                        ? $this->getVehicleLastCoordinates($fuelCard)
                        : null;

                    $fuelCard
                        ->setStatus(FuelCard::STATUS_ACTIVE)
                        ->setFuelPrice($fuelPrice)
                        ->setDriver($driver)
                        ->setVehicleCoordinates($lastVehicleCoordinates);

                    // todo keep for further
//                if ($fuelCard->getVehicle()
//                    && $this->settingService->isAllowGeolocationForFuelStation($fuelCard->getVehicle())
//                ) {
//                    $petrolStationCoordinates = $this->mapService
//                        ->getCoordinatesByLocation($fuelCard->getPetrolStation());
//
//                    if ($petrolStationCoordinates) {
//                        $fuelCard->setPetrolStationCoordinates($petrolStationCoordinates);
//                    }
//                }
                }

                if (($counter % self::BATCH_SIZE) === 0) {
                    $this->em->flush();
                    $this->em->clear();
                }
                ++$counter;
            }

            $this->em->flush();
        } catch (\Throwable $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
        }
    }

    /**
     * @param FuelCard $fuelCard
     * @return object|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function findDriverByVehicle(FuelCard $fuelCard)
    {
        if ($fuelCard->getVehicle() && $fuelCard->getTransactionDate()) {
            $driverHistory = $this->em->getRepository(DriverHistory::class)->findDriverByDateRange(
                $fuelCard->getVehicle(),
                $fuelCard->getTransactionDate()
            );

            return $driverHistory
                ? $this->em->getRepository(User::class)->findOneBy(['id' => $driverHistory->getDriver()])
                : null;
        }

        return null;
    }

    /**
     * @param FuelCard $fuelCard
     * @return \App\Entity\Tracker\TrackerHistoryLast|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    private function getVehicleLastCoordinates(FuelCard $fuelCard)
    {
        if ($fuelCard->getTransactionDate() && $fuelCard->getVehicle() && $fuelCard->getVehicle()->getDevice()) {
            $transactionDate = $fuelCard->getTransactionDate();
            $device = $this->em->getRepository(Device::class)
                ->getDeviceByVehicleFromDate($fuelCard->getVehicle(), $transactionDate);

            return $device
                ? $this->em->getRepository(TrackerHistory::class)
                    ->getLastTrackerCoordinatesByDevice($device, $transactionDate)
                : null;
        }

        return null;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function checkDisplayAdditionalFields(User $user): bool
    {
        $teamId = $user->getTeamId();
        if ($teamId) {
            return $this->fuelCardRepo->checkAdditionalFields($teamId);
        }
        return false;
    }

    public function updateRecord(FuelCard $fuelCard, array $data, User $user): FuelCard
    {
        if ($data['vehicleId'] ?? null) {
            $vehicle = $this->em->getRepository(Vehicle::class)->find($data['vehicleId']);
            if ($vehicle->getTeam()->getId() == $fuelCard->getTeamId()) {
                $fuelCard->setVehicle($vehicle);
                $fuelCard->setDriver($vehicle->getDriver());
                $fuelCard->setUpdatedAt(new \DateTime());
                $fuelCard->setUpdatedBy($user);
                $this->em->flush();
            }
        }

        return $fuelCard;
    }
}
