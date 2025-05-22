<?php

namespace App\Service\Vehicle;

use App\Entity\Vehicle;
use App\Entity\User;
use App\Entity\VehicleEngineHours;
use App\Service\BaseService;
use App\Service\Redis\MemoryDbService;
use App\Service\Redis\Models\VehicleRedisModel;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;

class VehicleEngineHoursService extends BaseService
{
    protected $translator;
    private $em;
    private $validator;
    private $paginator;
    private $notificationDispatcher;
    private $vehicleService;
    private $memoryDb;

    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        ValidatorInterface $validator,
        PaginatorInterface $paginator,
        NotificationEventDispatcher $notificationDispatcher,
        VehicleService $vehicleService,
        MemoryDbService $memoryDb
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->validator = $validator;
        $this->paginator = $paginator;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->vehicleService = $vehicleService;
        $this->memoryDb = $memoryDb;
    }

    public function saveByVehicleAndDataAndUser(Vehicle $vehicle, array $data, User $user): VehicleEngineHours
    {
        $vehicleEngineHours = new VehicleEngineHours($data);
        $vehicleEngineHours->setVehicle($vehicle);
        $vehicleEngineHours->setCreatedBy($user);

        // @todo uncomment if it should be via storage with more accuracy
//        $vehicleEngineOnTimeFromStorage = $this->memoryDb->get(VehicleRedisModel::getEngineOnTimeKey($vehicle));
//        $vehicleEngineOnTime = $vehicleEngineOnTimeFromStorage ?: $vehicle->getEngineOnTime();
//        $vehicleEngineHours->setPrevEngineHours($vehicleEngineOnTime);
        $vehicleEngineHours->setPrevEngineHours($vehicle->getEngineOnTime());

        $this->validate($this->validator, $vehicleEngineHours);
        $vehicle->setEngineOnTime($vehicleEngineHours->getEngineHours());
        $this->memoryDb->set(VehicleRedisModel::getEngineOnTimeKey($vehicle), $vehicleEngineHours->getEngineHours());

        $this->em->persist($vehicleEngineHours);
        $this->em->flush();

        return $vehicleEngineHours;
    }

    /**
     * @param Vehicle $vehicle
     * @return array|object[]
     */
    public function listByVehicle(Vehicle $vehicle)
    {
        return $this->em->getRepository(VehicleEngineHours::class)
            ->findBy(['vehicle' => $vehicle], ['createdAt' => 'DESC']);
    }

    public function delete($id, Vehicle $vehicle)
    {
        $engineHour = $this->em->getRepository(VehicleEngineHours::class)->findOneBy([
            'id' => $id,
            'vehicle' => $vehicle
        ]);

        if ($engineHour) {
            $vehicle->decreaseEngineOnTime($engineHour->getDiffValue());
            $this->em->remove($engineHour);
            $this->em->flush();
        }
    }

    public function getEngineHoursDataByVehicleAndOccurredAt(Vehicle $vehicle, $occurredAt = null): ?VehicleEngineHours
    {
        $occurredAt = $occurredAt ? BaseService::parseDateToUTC($occurredAt) : null;
        $lastTR = $this->vehicleService->getLastTrackerRecordByVehicle($vehicle, $occurredAt);

        /** @var VehicleEngineHours $vehicleEngineHours */
        $vehicleEngineHours = $vehicle->getLastEngineHours();

        if (!$vehicleEngineHours) {
            $vehicleEngineHours = new VehicleEngineHours();
            $vehicleEngineHours->setVehicle($vehicle);
            $vehicleEngineHours->setCreatedAt(null);
            $vehicleEngineHours->setEngineHours($vehicle->getEngineOnTime());
        }

        if ($lastTR) {
            $vehicleEngineHours->setCreatedAt(max($lastTR->getTs(), $vehicleEngineHours->getCreatedAt()));
        }

        return $vehicleEngineHours;
    }
}
