<?php

namespace App\Service\EventLog;

use App\Entity\Notification\Event;
use App\Entity\EventLog\EventLog;
use App\Events\Notification\NotificationEvent;
use App\Service\EventLog\Manager\EventLogManager;
use App\Service\BaseService;
use App\Service\EventLog\Factory\EventLogFactory;
use App\Service\MapService\MapServiceResolver;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventLogService extends BaseService
{
    private $em;
    private $evenLogFinder;
    private $eventLogManager;
    protected $translator;
    private $mapService;

    public function __construct(
        EntityManager $em,
//        TransformedFinder $evenLogFinder,
        EventLogManager $eventLogManager,
        TranslatorInterface $translator,
        MapServiceResolver $mapServiceResolver
    ) {
        $this->em = $em;
//        $this->evenLogFinder = $evenLogFinder;
        $this->eventLogManager = $eventLogManager;
        $this->translator = $translator;
        $this->mapService = $mapServiceResolver->getInstance();
    }

    /**
     * @param Event $event
     * @param NotificationEvent $entity
     * @return EventLog
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(Event $event, NotificationEvent $entity)
    {
        try {
            $entityData = EventLogFactory::getInstance($event, $entity, $this->mapService);

            $obj = $this->eventLogManager->create(
                $entityData->getDetails(),
                $entityData->getTriggeredDetails(),
                $entityData->getTriggeredByDetails(),
                $entityData->getEventSourceType(),
                $entityData->getEventDetails(),
                $entityData->getEntityCurrentAction(),
                $event,
                $entityData->getEventDate(),
                $entityData->getEntityTeam(),
                $entityData->getVehicleId(),
                $entityData->getDeviceId(),
                $entityData->getDriverId(),
                $entityData->getShortDetails(),
                $entityData->getEntityId(),
                $entityData->getEntityTeamId(),
                $entityData->getTeamBy(),
                $entityData->getUserBy()
            );
            $this->em->persist($obj);
            $this->em->flush();

            return $obj;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param EventLog $eventLog
     * @param array $notifications
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function update(EventLog $eventLog, $notifications)
    {
        $result = [];
        foreach ($notifications as $notification) {
            $result[] = $notification->getId();
        }

        $eventLog->setNotificationsList($result)->setUpdatedAt(new \DateTime());
        $this->em->persist($eventLog);
        $this->em->flush();
    }

    /**
     * @param $eventLog
     * @throws \Doctrine\ORM\ORMException
     */
    public function delete($eventLog)
    {
        $this->em->remove($eventLog);
        $this->em->flush();
    }
}
