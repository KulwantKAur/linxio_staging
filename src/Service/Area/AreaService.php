<?php

namespace App\Service\Area;

use App\Entity\Area;
use App\Entity\AreaGroup;
use App\Entity\AreaHistory;
use App\Entity\FuelStation;
use App\Entity\Team;
use App\Entity\User;
use App\EntityManager\SlaveEntityManager;
use App\Enums\EntityHistoryTypes;
use App\Events\Area\AreaArchivedEvent;
use App\Events\Area\AreaCreatedEvent;
use App\Events\Area\AreaDeletedEvent;
use App\Events\Area\AreaUpdatedEvent;
use App\Service\BaseService;
use App\Service\Client\ClientService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Validation\ValidationService;
use App\Service\Vehicle\VehicleService;
use App\Util\GeoHelper;
use App\Util\RequestFilterResolver\RequestFilterResolver;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Vehicle;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use App\Entity\File as SpreadSheetFile;
use App\Service\Device\Factory\FileMapperFactory;
use App\Service\File\Factory\FileReaderFactory;
use App\Service\FuelCard\Mapper\BaseFileMapper;
use App\Service\FuelCard\Mapper\FileMapperManager;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\Client;

class AreaService extends BaseService
{
    use AreaServiceHelperTrait;
    use AreaServiceFieldsTrait;

    protected $translator;
    private $em;
    private $areaFinder;
    private $eventDispatcher;
    private $validationService;
    private $entityHistoryService;
    private $vehicleService;
    private $emSlave;

    public const ELASTIC_NESTED_FIELDS = [];
    public const ELASTIC_SIMPLE_FIELDS = [
        'id' => 'id',
        'name' => 'name',
        'teamId' => 'teamId',
        'groups' => 'groups.id',
        'status' => 'status'
    ];
    public const ELASTIC_RANGE_FIELDS = [];

    public const FIELDS_IN_SECONDS = ['totalTime', 'averageTime', 'parkingTime', 'idlingTime'];
    public const FIELDS_TO_CONVERT = ['arrivedAt', 'departedAt'];

    /**
     * @param TranslatorInterface $translator
     * @param EntityManager $em
     * @param TransformedFinder $areaFinder
     * @param EventDispatcherInterface $eventDispatcher
     * @param ValidationService $validationService
     * @param EntityHistoryService $entityHistoryService
     * @param VehicleService $vehicleService
     * @param SlaveEntityManager $emSlave
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        TransformedFinder $areaFinder,
        EventDispatcherInterface $eventDispatcher,
        ValidationService $validationService,
        EntityHistoryService $entityHistoryService,
        VehicleService $vehicleService,
        SlaveEntityManager $emSlave
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->areaFinder = new ElasticSearch($areaFinder);
        $this->eventDispatcher = $eventDispatcher;
        $this->validationService = $validationService;
        $this->entityHistoryService = $entityHistoryService;
        $this->vehicleService = $vehicleService;
        $this->emSlave = $emSlave;
    }

    public function create(array $data, User $currentUser): Area
    {
        $this->validateAreaFields($data, $currentUser);
        $area = new Area($data);

        $area->setTeam($currentUser->getTeam());
        $area->setCreatedBy($currentUser);

        $groups = isset($data['groupIds']) ? $this->em->getRepository(AreaGroup::class)
            ->findBy(['id' => $data['groupIds'], 'team' => $currentUser->getTeam()]) : null;
        if (is_array($groups)) {
            $area = $this->addAreaToGroups($area, new ArrayCollection($groups));
        }

        $coordString = $this->convertCoordinates($data['coordinates']);
        $area->setPolygon($coordString);

        $this->em->persist($area);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new AreaCreatedEvent($area), AreaCreatedEvent::NAME);

        $this->createAreaHistoryForCurrentVehicles($area);

        $this->em->refresh($area);

        return $area;
    }

    /**
     * @param Area $area
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function createAreaHistoryForCurrentVehicles(Area $area)
    {
        $vehicleIds = $this->em->getRepository(Vehicle::class)->getVehicleIdListByTeam($area->getTeam());
        $trackerHistoryLast = $this->em->getRepository(Area::class)->findVehiclesInArea($area, $vehicleIds);

        foreach ($trackerHistoryLast as $thl) {
            $areaHistory = new AreaHistory(
                [
                    'area' => $area,
                    'vehicle' => $thl->getVehicle(),
                    'driverArrived' => $thl->getDriver(),
                    'arrived' => new \DateTime()
                ]
            );
            $this->em->persist($areaHistory);
        }

        $this->em->flush();
    }

    /**
     * @param array $coordinates
     * @return string
     */
    public static function convertCoordinates(array $coordinates)
    {
        $data = [];
        foreach ($coordinates as $coordinate) {
            $data[] = $coordinate['lng'] . ' ' . $coordinate['lat'];
        }

        return implode(',', $data);
    }

    /**
     * @param array $params
     * @param User $user
     * @param bool $paginated
     * @return array
     */
    public function areaList(array $params, User $user, bool $paginated = true)
    {
        if ($user->isInClientTeam() || $user->isInResellerTeam()) {
            $params['teamId'] = $user->getTeam()->getId();
        }
        if ($user->isClientManager() && !$user->isAllTeamsPermissions()) {
            $params['teamId'] = $user->getManagedTeamsIds();
        }
        $params = Area::handleStatusParams($params);
        $params = $this->handleUserGroupParams($params, $user);

        $fields = $this->prepareElasticFields($params);

        return $this->areaFinder->find($fields, $fields['_source'] ?? [], $paginated);
    }

    /**
     * @param int $id
     * @param User $user
     * @return Area|null
     */
    public function getById(int $id, User $user): ?Area
    {
        return $this->em->getRepository(Area::class)->getById($id, $user);
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @param Area $area
     * @return Area
     * @throws \Exception
     */
    public function edit(array $data, User $currentUser, Area $area): Area
    {
        if ($area->getType() === Area::TYPE_FUEL_STATION) {
            return $area;
        }

        $this->validateAreaFields($data, $currentUser);

        if ($data['coordinates'] ?? null) {
            $coordString = $this->convertCoordinates($data['coordinates']);
            $area->setPolygon($coordString);
        }
        $groups = isset($data['groupIds']) ? $this->em->getRepository(AreaGroup::class)
            ->findBy(['id' => $data['groupIds'], 'team' => $currentUser->getTeam()]) : null;
        if (is_array($groups)) {
            $area = $this->addAreaToGroups($area, new ArrayCollection($groups));
        }

        $data['updatedAt'] = new \DateTime();
        $area->setAttributes($data);
        $area->setUpdatedBy($currentUser);

        $this->em->flush();

        $this->em->refresh($area);

        $this->eventDispatcher->dispatch(new AreaUpdatedEvent($area), AreaUpdatedEvent::NAME);

        return $area;
    }

    public function remove(Area $area, User $currentUser)
    {
        if ($area->getType() === Area::TYPE_FUEL_STATION) {
            return $area;
        }

        $area->setStatus(Area::STATUS_DELETED);

        $area->setUpdatedAt(new \DateTime());
        $area->setUpdatedBy($currentUser);

        $this->em->getRepository(AreaHistory::class)->setDepartedByArea($area);

        $this->em->flush();

        $this->eventDispatcher->dispatch(new AreaDeletedEvent($area), AreaDeletedEvent::NAME);
    }

    /**
     * @param Area $area
     * @param User $currentUser
     * @return Area
     * @throws \Exception
     */
    public function restore(Area $area, User $currentUser)
    {
        if ($area->getStatus() === Area::STATUS_ARCHIVE) {
            $statusHistory = $this->entityHistoryService->listWithExclude(
                Area::class,
                $area->getId(),
                EntityHistoryTypes::AREA_STATUS,
                [Area::STATUS_ARCHIVE]
            )->first();
            if ($statusHistory) {
                $area->setStatus($statusHistory->getPayload());
            } else {
                $area->setStatus(Area::STATUS_ACTIVE);
            }
            $area->setUpdatedAt(new \DateTime());
            $area->setUpdatedBy($currentUser);

            $this->em->flush();

            $this->eventDispatcher->dispatch(new AreaUpdatedEvent($area), AreaUpdatedEvent::NAME);
        }

        return $area;
    }

    public function archive(Area $area, User $currentUser)
    {
        if ($area->getType() === Area::TYPE_FUEL_STATION) {
            return $area;
        }

        $area->setStatus(Area::STATUS_ARCHIVE);

        $area->setUpdatedAt(new \DateTime());
        $area->setUpdatedBy($currentUser);

        $this->em->getRepository(AreaHistory::class)->setDepartedByArea($area);

        $this->em->flush();

        $this->eventDispatcher->dispatch(new AreaArchivedEvent($area), AreaArchivedEvent::NAME);
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @return mixed
     */
    public function checkPointInArea(array $data, User $currentUser)
    {
        $point = implode(' ', $data['point']);
        if (($currentUser->isInAdminTeam() && !$currentUser->isClientManager())
            || $currentUser->isAllTeamsPermissions()) {
            $team = isset($data['teamId']) ? $this->em->getRepository(Team::class)->find($data['teamId']) : null;
        } elseif ($currentUser->isClientManager()) {
            $team = isset($data['teamId']) && $data['teamId'] && $currentUser->hasTeamPermission($data['teamId'])
                ? $this->em->getRepository(Team::class)->find($data['teamId'])
                : $currentUser->getManagedTeamsIds();
        } else {
            $team = $currentUser->getTeam();
        }

        return $this->em->getRepository(Area::class)->findByPoint($point, $team);
    }

    private function addAreaToGroups(Area $area, ArrayCollection $groups): Area
    {
        $areaGroups = $area->getGroups();

        foreach ($areaGroups as $areaGroup) {
            if (!$groups->contains($areaGroup)) {
                $areaGroup->removeArea($area);
                $area->removeFromGroup($areaGroup);
            }
        }

        if ($groups->count()) {
            foreach ($groups as $group) {
                if (!$area->getGroups()->contains($group)) {
                    $group->addArea($area);
//                    $area->addToGroup($group); //hack only for response, does not impact to entity database data
                }
            }
        } elseif ($groups->count() === 0) {
            foreach ($areaGroups as $areaGroup) {
                $areaGroup->removeArea($area);
                $area->removeFromGroup($areaGroup);
            }
        }

        return $area;
    }

    public function createFuelStationArea(FuelStation $fuelStation, Team $team)
    {
        $coordinates = GeoHelper::coordinatesOfCircle([$fuelStation->getLat(), $fuelStation->getLng()], 100, 12);
        $data = [
            'name' => $fuelStation->getStationName(),
            'coordinates' => $coordinates,
            'createdAt' => new \DateTime(),
            'type' => Area::TYPE_FUEL_STATION,
            'externalId' => $fuelStation->getId()
        ];

        $area = new Area($data);
        $area->setPolygon($this->convertCoordinates($coordinates));
        $area->setTeam($team);

        $this->em->persist($area);
        $this->em->flush();

        return $area;
    }
    
     public function areaCustomImport(int $cid, UploadedFile $fileData, string $destination) 
    {
        $client = $this->em->getRepository(Client::class)->find($cid);
        $clientUser = $client->getKeyContact();
        $teamID = $client->getTeamId();
        
        $uniqueFilename = md5(uniqid()).'.'.$fileData->getClientOriginalExtension();
        $fileData->move($destination, $uniqueFilename);

        $uFile = new UploadedFile($destination.$uniqueFilename, $destination);

        $csvFile = new SpreadSheetFile($uFile, NULL);
        $csvData = $this->processCSV($csvFile);

        $insertData = [];
        $cnt = 0;
        
        foreach ($csvData as $key => $value) {
            $insertData[$cnt] = [  
                'polygon' => $value[0],  
                'coordinates' => $this->formatCoordinates($value[1]),  
                'name' => $value[2],  
                'teamId' => $teamID,  
             ]; 
            $this->create($insertData[$cnt], $clientUser);
            $cnt++;
        }

        return $insertData;
    }

    private function processCSV(SpreadSheetFile $csvFile) 
    {
        $spreadsheet = $this->getSpreadSheet($csvFile);
        $data = array_slice($spreadsheet, 1);

        return $data;
    }

    private function getSpreadSheet(SpreadSheetFile $fileEntity)
    {
        $resource = $fileEntity->getPath().$fileEntity->getName();
        Cell::setValueBinder(new StringValueBinder());
        $reader = FileReaderFactory::getInstance($fileEntity);

        return $reader->load($resource)->getActiveSheet()->toArray();
    }

    private function formatCoordinates(string $rawCoordinates): ?array {
        $coordinates = explode(',', $rawCoordinates);  
        $formattedCoordinates = [];  
  
        for ($i = 0; $i < count($coordinates); $i += 2) {  
           $formattedCoordinates[] = [  
              'lat' => $coordinates[$i],  
              'lng' => $coordinates[$i+1],  
           ];  
        }

        return $formattedCoordinates;
    }
}
