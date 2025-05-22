<?php

namespace App\Service\Device\Import;

use App\Entity\BaseEntity;
use App\Entity\Client;
use App\Entity\Device;
use App\Entity\DeviceInstallation;
use App\Entity\DeviceModel;
use App\Entity\DeviceVendor;
use App\Entity\File;
use App\Entity\Reseller;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleType;
use App\Events\Device\DeviceChangedTeamEvent;
use App\Events\Device\DeviceContractChangedEvent;
use App\Events\Device\DeviceCreatedEvent;
use App\Events\Vehicle\VehicleCreatedEvent;
use App\Service\BaseService;
use App\Service\Device\Factory\FileMapperFactory;
use App\Service\Device\Traits\DeviceImportFieldsTrait;
use App\Service\File\Factory\FileReaderFactory;
use App\Service\FuelCard\Mapper\BaseFileMapper;
use App\Service\FuelCard\Mapper\FileMapperManager;
use App\Util\DateHelper;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

class DevicesVehiclesImport extends BaseService
{
    use DeviceImportFieldsTrait;

    protected $translator;
    private $em;
    private $skipLine;
    private $teamId;
    private $validator;
    private $deviceRepo;
    private $vehicleRepo;
    private $eventDispatcher;

    private function updateDeviceWithNewTeam(Device $device, Team $newTeam, bool $recalcContractDate = true): Device
    {
        if ($device->getTeamId() !== $newTeam->getId()) {
            $device->setTeam($newTeam);

            if ($recalcContractDate) {
                $device->recalculateContractDate();
            }

            $this->eventDispatcher
                ->dispatch(new DeviceChangedTeamEvent($device), DeviceChangedTeamEvent::NAME);
            $this->eventDispatcher
                ->dispatch(new DeviceContractChangedEvent($device), DeviceContractChangedEvent::NAME);
        }

        return $device;
    }

    private function updateDataImportFieldsByPreparedData(array $dataImport, array $preparedData): array
    {
        $dataImport['contractStart'] = DateHelper::formatDate($preparedData['contractStart']);
        $dataImport['expDate'] = DateHelper::formatDate($preparedData['expDate']);

        return $dataImport;
    }

    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->validator = $validator;
        $this->deviceRepo = $em->getRepository(Device::class);
        $this->vehicleRepo = $em->getRepository(Vehicle::class);
        $this->eventDispatcher = $eventDispatcher;
    }

    public function importFile(File $fileEntity, User $currentUser, ObjectPersister $clientObjectPersister)
    {
        $data = [];
        $clientsForUpdate = [];
        $spreadsheet = $this->getSpreadSheet($fileEntity);
        $fieldsForMapping = FileMapperFactory::getFieldsForMapping($fileEntity, $this->translator);
        $fileMapperObj = (new FileMapperManager())->getMapperObj($fieldsForMapping, $spreadsheet);

        if ($fileMapperObj->getHeader()) {
            foreach ($spreadsheet as $key => $line) {
                $this->skipLine = $key === 0;
                /** @var array $dataImport */
                $dataImport = $this->prepareRow($fileMapperObj, $line);

                if ($this->skipLine) {
                    continue;
                }
                $data[$key] = [];
                $data[$key] = ['row' => $key];
                $errors = $this->validateImportFields($dataImport);

                if (count($errors)) {
                    $data[$key]['errors'] = $errors;
                } else {
                    $preparedData = $this->prepareFields($dataImport, $currentUser)['data'];
                    $dataImport = $this->updateDataImportFieldsByPreparedData($dataImport, $preparedData);
                    /** @var Device $device */
                    $device = $preparedData['device'];
                    /** @var Vehicle $vehicle */
                    $vehicle = $preparedData['vehicle'] ?? null;
                    $deviceInstallation = $this->em->getRepository(DeviceInstallation::class)
                        ->findOneBy(['device' => $device, 'vehicle' => $vehicle, 'uninstallDate' => null]);

                    if ($device) {
                        if ($vehicle && !$deviceInstallation) {
                            $deviceInstallation = new DeviceInstallation(
                                [
                                    'vehicle' => $vehicle,
                                    'device' => $device,
                                    'installDate' => new \DateTime(),
                                    'odometer' => null
                                ]
                            );
                            $device->setVehicle($vehicle);
                            $vehicle->setDevice($device);
                            $this->em->persist($deviceInstallation);
                            $device->setUpdatedAt(new \DateTime());
                            $device->setUpdatedBy($currentUser);
                            $device = $this->updateDeviceWithNewTeam($device, $vehicle->getTeam());
                            $device->setStatus(Device::STATUS_OFFLINE);
                        }
                    }

                    $clientsForUpdate = $this->createClientArrayForUpdate($device, $clientsForUpdate);
                    $clientsForUpdate = $this->createClientArrayForUpdate($vehicle, $clientsForUpdate);
                }

                $data[$key] = array_merge($data[$key], $dataImport);

                $this->em->flush();

                if ($clientsForUpdate) {
                    $clientObjectPersister->replaceMany($clientsForUpdate);
                }
            }
        }

        return $data;
    }

    private function createClientArrayForUpdate(?BaseEntity $entity, array $clientsForUpdate): array
    {
        if ($entity && $entity->getTeam()->getClient()
            && !array_key_exists($entity->getTeam()->getClientId(), $clientsForUpdate)) {
            $clientsForUpdate[] = $entity->getTeam()->getClient();
        }

        return $clientsForUpdate;
    }

    public function importData(
        File $fileEntity,
        User $currentUser,
        ObjectPersister $vehiclePersister,
        ObjectPersister $devicePersister
    ) {
        $data = [];
        $spreadsheet = $this->getSpreadSheet($fileEntity);

        $fieldsForMapping = FileMapperFactory::getFieldsForMapping($fileEntity, $this->translator);

        $fileMapperObj = (new FileMapperManager())->getMapperObj($fieldsForMapping, $spreadsheet);

        $this->em->beginTransaction();  //simulate import devices/vehicles to database and validate
        $elasticaData = ['vehicles' => ['new' => [], 'old' => []], 'devices' => ['new' => [], 'old' => []]];

        if ($fileMapperObj->getHeader()) {
            try {
                foreach ($spreadsheet as $key => $line) {
                    $this->skipLine = $key === 0;
                    /** @var array $dataImport */
                    $dataImport = $this->prepareRow($fileMapperObj, $line);

                    if ($this->skipLine) {
                        continue;
                    }
                    $data[$key] = [];
                    $data[$key] = ['row' => $key];
                    $errors = $this->validateImportFields($dataImport);

                    if (in_array($this->translator->trans('entities.client.unknownClientId'), $errors)) {
                        $dataImport['clientId'] = null;
                    }
                    if (in_array($this->translator->trans('entities.reseller.unknownResellerId'), $errors)) {
                        $dataImport['resellerId'] = null;
                    }

                    if (count($errors)) {
                        $data[$key]['errors'] = $errors;
                    } else {
                        $filteredData = $this->prepareFields($dataImport, $currentUser, false);
                        $dataImport = $this->updateDataImportFieldsByPreparedData($dataImport, $filteredData['data']);
                        $preparedData = $filteredData['data'];
                        $elasticaData = array_merge_recursive($elasticaData, $filteredData['elasticaData']);
                        $device = $preparedData['device'];
                        $vehicle = $preparedData['vehicle'] ?? null;
                        $deviceInstallation = $this->em->getRepository(DeviceInstallation::class)
                            ->findOneBy(['device' => $device, 'vehicle' => $vehicle, 'uninstallDate' => null]);

                        if ($device) {
                            if ($vehicle && !$deviceInstallation) {
                                $deviceInstallation = new DeviceInstallation(
                                    [
                                        'vehicle' => $vehicle,
                                        'device' => $device,
                                        'installDate' => new \DateTime(),
                                        'odometer' => null
                                    ]
                                );
                                $device->setVehicle($vehicle);
                                $vehicle->setDevice($device);
                                $this->em->persist($deviceInstallation);
                                $device->setUpdatedAt(new \DateTime());
                                $device->setUpdatedBy($currentUser);
                                $device = $this->updateDeviceWithNewTeam($device, $vehicle->getTeam());
                                $device->setStatus(Device::STATUS_OFFLINE);
                            }
                        }
                    }

                    $data[$key] = array_merge($data[$key], $dataImport);
                    $this->em->flush();
                }
            } catch (\Exception $e) {
                $this->em->rollback();  //roll back import simulation
                $this->refreshDevicesVehiclesIndex($elasticaData, $vehiclePersister, $devicePersister);
                throw $e;
            }
        }
        $this->em->rollback();  //roll back import simulation

        $this->refreshDevicesVehiclesIndex($elasticaData, $vehiclePersister, $devicePersister);

        return $data;
    }

    /**
     * @param $fileEntity
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function getSpreadSheet($fileEntity)
    {
        $resource = $fileEntity->getPath() . $fileEntity->getName();
        $this->teamId = $fileEntity->getTeamId();
        Cell::setValueBinder(new StringValueBinder());
        $reader = FileReaderFactory::getInstance($fileEntity);

        return $reader->load($resource)->getActiveSheet()->toArray();
    }

    /**
     * @param $elasticaData
     * @param $vehiclePersister
     * @param $devicePersister
     */
    public function refreshDevicesVehiclesIndex($elasticaData, $vehiclePersister, $devicePersister)
    {
        foreach ($elasticaData['vehicles']['old'] as $vehicle) {
            $this->em->refresh($vehicle);
            $vehiclePersister->replaceOne($vehicle);
        }
        foreach ($elasticaData['devices']['old'] as $device) {
            $this->em->refresh($device);
            $devicePersister->replaceOne($device);
        }
        if (count($elasticaData['vehicles']['new'])) {
            $vehiclePersister->deleteMany($elasticaData['vehicles']['new']);
        }
        if (count($elasticaData['devices']['new'])) {
            $devicePersister->deleteMany($elasticaData['devices']['new']);
        }
    }

    /**
     * @param BaseFileMapper $fileMapperObj
     * @param $row
     * @return array|bool
     * @throws NonUniqueResultException
     */
    public function prepareRow(BaseFileMapper $fileMapperObj, $row)
    {
        $data = [];
        foreach ($fileMapperObj->getHeader() as $index => $propertyName) {
            try {
                if (ctype_space($row[$index])) {
                    $data[$propertyName] = null;
                } else {
                    $data[$propertyName] = trim($row[$index]);
                }
            } catch (Exception $e) {
                throw new NotFoundHttpException(
                    $this->translator->trans('validation.errors.import.fields_not_recognized')
                );
            }
        }

        return $fileMapperObj->specialPrepareFields($data);
    }

    public function prepareFields(array $data, User $currentUser, bool $save = true): array
    {
        $elasticaData = ['vehicles' => ['new' => [], 'old' => []], 'devices' => ['new' => [], 'old' => []]];
        $team = isset($data['clientId'])
            ? $this->em->getRepository(Client::class)->find($data['clientId'])->getTeam()
            : $currentUser->getTeam();

        if (!isset($data['clientId']) && isset($data['resellerId'])) {
            $team = $this->em->getRepository(Reseller::class)->find($data['resellerId'])->getTeam();
        }

        $data['team'] = $team;
        $deviceVendor = $this->findVendor($data['deviceVendor']);
        $deviceModel = $deviceVendor ? $this->findModel($deviceVendor, $data['deviceModel']) : null;
        $device = $this->findDevice($data['deviceImei']) ?? null;
        $timezone = $team->getClient()?->getTimeZoneName();
        $contractStartAt = isset($data['contractStart']) && $data['contractStart']
            ? Carbon::createFromFormat('d/m/Y', $data['contractStart'])->startOfDay()
            : null;
        $contractFinishAt = isset($data['expDate']) && $data['expDate']
            ? Carbon::createFromFormat('d/m/Y', $data['expDate'])->endOfDay()
            : null;

        if ($timezone) {
            $contractStartAt?->shiftTimezone($timezone)->setTimezone('UTC');
            $contractFinishAt?->shiftTimezone($timezone)->setTimezone('UTC');
        }

        $data['contractStart'] = $contractStartAt;
        $data['expDate'] = $contractFinishAt;

        if (!$device && $deviceModel && $data['deviceImei']) {
            $device = new Device(
                [
                    'model' => $deviceModel,
                    'team' => $team,
                    'imei' => $data['deviceImei'],
                    'imsi' => $data['deviceImsi'] ?? null,
                    'phone' => $data['devicePhone'] ?? null,
                    'createdBy' => $currentUser,
                    'contractFinishAt' => $contractFinishAt,
                    'contractStartAt' => $contractStartAt,
                    'contractId' => $data['contractId'] ?? null,
                    'ownership' => $data['ownership'] ?? null,
                ]
            );
            if (!$contractFinishAt) {
                $device->recalculateContractDate();
            }
            $device->setUsage($deviceModel->getUsage());
            $this->em->persist($device);
            $elasticaData['devices']['new'][] = $device;

            if ($save) {
                $this->eventDispatcher->dispatch(new DeviceCreatedEvent($device), DeviceCreatedEvent::NAME);
                $this->eventDispatcher
                    ->dispatch(new DeviceChangedTeamEvent($device), DeviceChangedTeamEvent::NAME);
            }
        } elseif ($device) {
            if ($deviceModel ?? null) {
                $device->setModel($deviceModel);
                $device->setUsage($deviceModel->getUsage());
            }
            if ($data['devicePhone'] ?? null) {
                $device->setPhone($data['devicePhone']);
            }
            if ($data['deviceImsi'] ?? null) {
                $device->setImsi($data['deviceImsi']);
            }
            if ($data['contractId'] ?? null) {
                $device->setContractId($data['contractId']);
            }
            if ($contractFinishAt ?? null) {
                $device->setContractFinishAt($contractFinishAt);
            }
            if ($data['ownership'] ?? null) {
                $device->setOwnership($data['ownership']);
            }
            if ($team) {
                $device->setTeam($team);
            }

            $elasticaData['devices']['old'][] = $device;
        }
        $data['device'] = $device;

        if (isset($data['vehicleRegNo'])) {
            $vehicle = $this->findVehicle($data['vehicleRegNo']);

            if (!$vehicle && $team->isClientTeam() && isset($data['vehicleType']) && isset($data['vehicleTitle'])) {
                $vehicleType = $this->em->getRepository(VehicleType::class)
                    ->getVehiclesTypeByName(strtolower($data['vehicleType']));
                $vehicle = new Vehicle(
                    [
                        'type' => $vehicleType,
                        'make' => $data['vehicleMake'] ?? null,
                        'makeModel' => $data['vehicleModel'] ?? null,
                        'year' => empty($data['vehicleYear']) ? null : $data['vehicleYear'],
                        'team' => $team,
                        'regNo' => $data['vehicleRegNo'],
                        'defaultLabel' => $data['vehicleTitle'],
                        'createdBy' => $currentUser
                    ]
                );
                $this->em->persist($vehicle);
                $elasticaData['vehicles']['new'][] = $vehicle;
                if ($save) {
                    $this->eventDispatcher->dispatch(new VehicleCreatedEvent($vehicle), VehicleCreatedEvent::NAME);
                }
            } elseif ($vehicle && $vehicle->getTeam()->isAdminTeam() && $team->isClientTeam()) {
                $vehicle->setTeam($team);
                $elasticaData['vehicles']['old'][] = $vehicle;
            }
            $data['vehicle'] = $vehicle;
        }

        return ['data' => $data, 'elasticaData' => $elasticaData];
    }

    /**
     * @param $imei
     * @return Device|object|null
     */
    public function findDevice($imei)
    {
        if (!$imei) {
            return null;
        }

        return $this->em->getRepository(Device::class)->getDeviceByImei($imei);
    }

    /**
     * @param $regNo
     * @return object|null
     */
    public function findVehicle($regNo)
    {
        if (!$regNo) {
            return null;
        }

        return $this->em->getRepository(Vehicle::class)->getVehicleByRegNo($regNo);
    }

    /**
     * @param $vendor
     * @return object|null
     */
    public function findVendor($vendor)
    {
        if (!$vendor) {
            return null;
        }

        return $this->em->getRepository(DeviceVendor::class)->findVendor($vendor);
    }

    /**
     * @param DeviceVendor $vendor
     * @param $model
     * @return object|null
     */
    public function findModel(DeviceVendor $vendor, $model)
    {
        if (!$model) {
            return null;
        }

        return $this->em->getRepository(DeviceModel::class)->getModel($vendor, $model);
    }
}
