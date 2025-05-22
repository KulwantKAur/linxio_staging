<?php

namespace App\Service\Device\Import;

use App\Entity\Client;
use App\Entity\Depot;
use App\Entity\Device;
use App\Entity\DeviceInstallation;
use App\Entity\DeviceModel;
use App\Entity\DeviceVendor;
use App\Entity\File;
use App\Entity\Reseller;
use App\Entity\Role;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use App\Entity\VehicleType;
use App\Events\Depot\DepotCreatedEvent;
use App\Events\Device\DeviceUninstalledEvent;
use App\Events\User\UserCreatedEvent;
use App\Events\UserGroup\UserGroupCreatedEvent;
use App\Events\Vehicle\VehicleCreatedEvent;
use App\Service\BaseService;
use App\Service\Device\Factory\FileMapperFactory;
use App\Service\Device\Traits\DeviceImportFieldsTrait;
use App\Service\File\Factory\FileReaderFactory;
use App\Service\FuelCard\Mapper\BaseFileMapper;
use App\Service\FuelCard\Mapper\FileMapperManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DevicesVehiclesDriverImport extends BaseService
{
    use DeviceImportFieldsTrait;

    private $teamId;

    private function uninstallExistingEntities(Device $device, Vehicle $vehicle, User $currentUser): void
    {
        $deviceInstallationByDevice = $this->em->getRepository(DeviceInstallation::class)
            ->findOneBy(['device' => $device, 'uninstallDate' => null]);
        $deviceInstallationByVehicle = $this->em->getRepository(DeviceInstallation::class)
            ->findOneBy(['vehicle' => $vehicle, 'uninstallDate' => null]);

        if ($deviceInstallationByDevice) {
            $this->eventDispatcher->dispatch(
                new DeviceUninstalledEvent($deviceInstallationByDevice, $currentUser), DeviceUninstalledEvent::NAME
            );
        }
        if ($deviceInstallationByVehicle) {
            $this->eventDispatcher->dispatch(
                new DeviceUninstalledEvent($deviceInstallationByVehicle, $currentUser), DeviceUninstalledEvent::NAME
            );
        }
    }

    public function __construct(
        private ObjectPersister          $vehiclePersister,
        private ObjectPersister          $devicePersister,
        private ObjectPersister          $userPersister,
        private EntityManagerInterface   $em,
        private ValidatorInterface       $validator,
        private EventDispatcherInterface $eventDispatcher,
        protected TranslatorInterface    $translator,
    ) {

    }

    public function importData(
        File $file,
        User $currentUser,
        array $params
    ) {
        $data = [];
        $this->withReinstall = boolval($params['withReinstall'] ?? false);
        $this->ignoreElasticSearch = boolval($params['ignoreElasticSearch'] ?? false);
        $spreadsheet = $this->getSpreadSheet($file);
        $fieldsForMapping = FileMapperFactory::getFieldsForMapping2($file, $this->translator);
        $fileMapperObj = (new FileMapperManager())->getMapperObj($fieldsForMapping, $spreadsheet);
        $elasticaData = ['vehicles' => ['new' => [], 'old' => []], 'drivers' => ['new' => [], 'old' => []]];

        if ($fileMapperObj->getHeader()) {
            try {
                foreach ($spreadsheet as $key => $line) {
                    if ($key === 0) {
                        continue;
                    }

                    /** @var array $dataImport */
                    $dataImport = $this->prepareRow($fileMapperObj, $line);
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
                        $preparedData = $filteredData['data'];
                        $elasticaData = array_merge_recursive($elasticaData, $filteredData['elasticaData']);
                        /** @var Device $device */
                        $device = $preparedData['device'];
                        /** @var Vehicle $vehicle */
                        $vehicle = $preparedData['vehicle'] ?? null;
                        /** @var User $driver */
                        $driver = $preparedData['driver'] ?? null;
                        /** @var UserGroup $userGroup */
                        $userGroup = $preparedData['userGroup'] ?? null;
                        /** @var Depot $vehicleDepot */
                        $vehicleDepot = $preparedData['vehicleDepot'] ?? null;
                        /** @var Team $team */
                        $team = $preparedData['team'] ?? null;
                        $deviceInstallation = $this->em->getRepository(DeviceInstallation::class)
                            ->findOneBy(['device' => $device, 'vehicle' => $vehicle, 'uninstallDate' => null]);

                        if ($device && $vehicle && !$deviceInstallation) {
                            if ($this->withReinstall) {
                                $this->uninstallExistingEntities($device, $vehicle, $currentUser);
                            }

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
                            $device->setTeam($vehicle->getTeam());
                        }
                        if ($driver && $userGroup) {
                            $userGroup->addVehicle($vehicle);
                            $userGroup->addUser($driver);
                            $driver->addToGroup($userGroup);

                            if ($vehicleDepot) {
                                $vehicleDepot->addToUserGroup($userGroup);
                                $userGroup->addDepot($vehicleDepot);
                                $vehicleDepot->addVehicle($vehicle);
                                $vehicle->setDepot($vehicleDepot);
                            }
                        }
                    }

                    $data[$key] = array_merge($data[$key], $dataImport);
                    $this->em->flush();
                }
            } catch (\Exception $e) {
                $this->refreshElasticaIndex($elasticaData);
                throw $e;
            }
        }

        $this->refreshElasticaIndex($elasticaData);

        return $data;
    }

    private function getSpreadSheet($fileEntity)
    {
        $resource = $fileEntity->getPath() . $fileEntity->getName();
        $this->teamId = $fileEntity->getTeamId();
        Cell::setValueBinder(new StringValueBinder());
        $reader = FileReaderFactory::getInstance($fileEntity);

        return $reader->load($resource)->getActiveSheet()->toArray();
    }

    public function refreshElasticaIndex($elasticaData)
    {
        if ($this->ignoreElasticSearch) {
            return;
        }
        foreach ($elasticaData['vehicles']['old'] as $vehicle) {
            $this->em->refresh($vehicle);
            $this->vehiclePersister->replaceOne($vehicle);
        }
        foreach ($elasticaData['drivers']['old'] as $device) {
            $this->em->refresh($device);
            $this->userPersister->replaceOne($device);
        }
        if (!$this->forSave) {
            if (count($elasticaData['vehicles']['new'])) {
                $this->vehiclePersister->deleteMany($elasticaData['vehicles']['new']);
            }
            if (count($elasticaData['drivers']['new'])) {
                $this->userPersister->deleteMany($elasticaData['drivers']['new']);
            }
        }
    }

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

        return $data;
    }

    public function prepareFields(array $data, User $currentUser, bool $withEvents = true)
    {
        $elasticaData = ['vehicles' => ['new' => [], 'old' => []], 'drivers' => ['new' => [], 'old' => []]];
        $team = isset($data['clientId'])
            ? $this->em->getRepository(Client::class)->find($data['clientId'])->getTeam()
            : $currentUser->getTeam();

        if (!isset($data['clientId']) && isset($data['resellerId'])) {
            $team = $this->em->getRepository(Reseller::class)->find($data['resellerId'])->getTeam();
        }

        $data['team'] = $team;
        $device = $this->findDevice($data['deviceImei']) ?? null;
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
                if ($withEvents) {
                    $this->eventDispatcher->dispatch(new VehicleCreatedEvent($vehicle), VehicleCreatedEvent::NAME);
                }
            } elseif ($vehicle && $vehicle->getTeam()->isAdminTeam() && $team->isClientTeam()) {
                $vehicle->setTeam($team);
                $elasticaData['vehicles']['old'][] = $vehicle;
            }
            $data['vehicle'] = $vehicle;
        }

        if (isset($data['driverEmail'])) {
            $driver = $this->findDriver($data['driverEmail']);

            if (!$driver) {
                $roleEntity = $this->em->getRepository(Role::class)->findOneBy(['name' => Role::ROLE_CLIENT_DRIVER]);
                $driver = new User(
                    [
                        'role' => $roleEntity,
                        'email' => $data['driverEmail'] ?? null,
                        'name' => $data['driverName'] ?? null,
                        'surname' => $data['driverSurname'] ?? null,
                        'phone' => $data['driverPhone'] ?? null,
                        'team' => $team,
                    ]
                );
                $this->em->persist($driver);
                $elasticaData['drivers']['new'][] = $driver;

                if ($withEvents) {
                    $this->eventDispatcher->dispatch(new UserCreatedEvent($driver), UserCreatedEvent::NAME);
                }
            } else {
                $driver->setTeam($team);
                $elasticaData['drivers']['old'][] = $driver;
            }

            $data['driver'] = $driver;
        }

        if (isset($data['userGroup'])) {
            $userGroup = $this->findUserGroup($data['userGroup'], $team);

            if (!$userGroup) {
                $userGroup = new UserGroup(
                    [
                        'name' => $data['userGroup'] ?? null,
                        'team' => $team,
                    ]
                );
                $this->em->persist($userGroup);

                if ($withEvents) {
                    $this->eventDispatcher->dispatch(
                        new UserGroupCreatedEvent($userGroup, $currentUser), UserGroupCreatedEvent::NAME
                    );
                }
            }

            $data['userGroup'] = $userGroup;
        }

        if (isset($data['vehicleDepot'])) {
            $vehicleDepot = $this->findVehicleDepot($data['vehicleDepot'], $team);

            if (!$vehicleDepot) {
                $vehicleDepot = new Depot(
                    [
                        'name' => $data['vehicleDepot'] ?? null,
                        'team' => $team,
                    ]
                );
                $this->em->persist($vehicleDepot);

                if ($withEvents) {
                    $this->eventDispatcher->dispatch(
                        new DepotCreatedEvent($vehicleDepot), DepotCreatedEvent::NAME
                    );
                }
            }

            $data['vehicleDepot'] = $vehicleDepot;
        }

        return ['data' => $data, 'elasticaData' => $elasticaData];
    }

    public function findDevice($imei)
    {
        if (!$imei) {
            return null;
        }

        return $this->em->getRepository(Device::class)->getDeviceByImei($imei);
    }

    public function findVehicle($regNo)
    {
        if (!$regNo) {
            return null;
        }

        return $this->em->getRepository(Vehicle::class)->getVehicleByRegNo($regNo);
    }

    public function findDriver($email)
    {
        if (!$email) {
            return null;
        }

        return $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    public function findUserGroup($userGroupName, Team $team)
    {
        if (!$userGroupName) {
            return null;
        }

        return $this->em->getRepository(UserGroup::class)->findOneBy(['name' => $userGroupName, 'team' => $team]);
    }

    public function findVehicleDepot($vehicleDepotName, Team $team)
    {
        if (!$vehicleDepotName) {
            return null;
        }

        return $this->em->getRepository(Depot::class)->findOneBy(['name' => $vehicleDepotName, 'team' => $team]);
    }

    public function findVendor($vendor)
    {
        if (!$vendor) {
            return null;
        }

        return $this->em->getRepository(DeviceVendor::class)->findVendor($vendor);
    }

    public function findModel(DeviceVendor $vendor, $model)
    {
        if (!$model) {
            return null;
        }

        return $this->em->getRepository(DeviceModel::class)->getModel($vendor, $model);
    }
}
