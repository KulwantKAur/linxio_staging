<?php

namespace App\Service\Notification;

use App\Entity\Acknowledge;
use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Notification;
use App\Entity\User;
use App\Events\Acknowledge\AcknowledgeCreatedEvent;
use App\Events\Acknowledge\AcknowledgeUpdatedEvent;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use Doctrine\ORM\EntityManager;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AcknowledgeService extends BaseService
{
    private $em;
    private $eventDispatcher;
    private $validator;

    /**
     * AcknowledgeService constructor.
     * @param EntityManager $em
     * @param EventDispatcherInterface $eventDispatcher
     * @param ValidatorInterface $validator
     */
    public function __construct(
        EntityManager $em,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator
    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
    }

    /**
     * @param EventLog $eventLog
     * @param Notification $notification
     * @param string $status
     * @return Acknowledge
     * @throws \Doctrine\ORM\ORMException
     */
    public function createAcknowledge(
        EventLog $eventLog,
        Notification $notification,
        string $status = Acknowledge::STATUS_OPEN
    ): Acknowledge {
        $acknowledge = new Acknowledge();
        $acknowledge->setEventLog($eventLog);
        $acknowledge->setNotification($notification);
        $acknowledge->setStatus($status);

        $this->em->persist($acknowledge);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new AcknowledgeCreatedEvent($acknowledge), AcknowledgeCreatedEvent::NAME);

        return $acknowledge;
    }

    /**
     * @param Acknowledge $acknowledge
     * @param User $currentUser
     * @param array $data
     * @return Acknowledge
     * @throws ValidationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateAcknowledge(Acknowledge $acknowledge, User $currentUser, array $data)
    {
        $acknowledge->setStatus($data['status']);

        if ($data['comment'] ?? null) {
            $acknowledge->setComment($data['comment']);
        }

        $this->validate($this->validator, $acknowledge);

        $this->em->flush();

        $this->eventDispatcher->dispatch(
            new AcknowledgeUpdatedEvent($acknowledge, $currentUser),
            AcknowledgeUpdatedEvent::NAME
        );

        return $acknowledge;
    }
}
