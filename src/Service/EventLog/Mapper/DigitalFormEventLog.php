<?php

namespace App\Service\EventLog\Mapper;

use App\Entity\DigitalFormAnswer;
use App\Entity\Notification\Event;
use App\Entity\User;
use App\Util\StringHelper;

class DigitalFormEventLog extends EventLog
{
    protected $context;

    /**
     * DigitalFormEventLog constructor.
     * @param DigitalFormAnswer $entity
     * @param User|null $currentUser
     * @param Event $event
     * @param array $context
     */
    public function __construct(DigitalFormAnswer $entity, ?User $currentUser, Event $event, $context = [])
    {
        $this->entity = $entity;
        $this->currentUser = $currentUser;
        $this->currentEvent = $event;
        $this->context = $context;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDetails()
    {
        $team['team'] = $this->entity->getVehicle() ? $this->entity->getVehicle()->getTeam()->toArray() : null;
        $context['context'] = $this->context ?? null;
        $entityDetails = $this->entity->toArray(DigitalFormAnswer::DEFAULT_DISPLAY_VALUES);

        return array_merge($entityDetails, $team, $context);
    }

    /**
     * @return string
     */
    public function getTeamData()
    {
        return $this->entity->getVehicle() ? $this->entity->getVehicle()->getTeam()->getClientName() : null;
    }

    /**
     * @return string
     */
    public function getEventSource()
    {
        return $this->entity->getDigitalForm()->getTitle();
    }

    /**
     * @return User|mixed
     */
    public function getEntityCurrentAction()
    {
        return $this->entityAction = $this->entity->getUser();
    }

    /**
     * @return \App\Entity\Team|null
     */
    public function getEntityTeam()
    {
        if ($this->entity->getUser()) {
            return $this->entity->getUser()->getTeam();
        } else {
            return $this->entity->getVehicle() ? $this->entity->getVehicle()->getTeam() : null;
        }
    }

    /**
     * @return \DateTime|null
     */
    public function getEventDate()
    {
        return $this->entity->getCreatedAt();
    }

    /**
     * @return string
     */
    public function getTriggeredDetails()
    {
        return $this->entity && $this->entity->getUser()
            ? $this->entity->getUser()->getEmail()
            : self::DEFAULT_TRIGGERED_BY;
    }

    /**
     * @return array|string[]
     */
    public function getTriggeredByDetails()
    {
        return ($this->entity && $this->entity->getUser())
            ? [
                'id' => $this->entity->getUser()->getId(),
                'value' => $this->entity->getUser()->getEmail()
            ]
            : ['value' => self::DEFAULT_TRIGGERED_BY];
    }

    /**
     * @return int|null
     */
    public function getVehicleId()
    {
        return $this->entity?->getVehicle()?->getId();
    }

    /**
     * @return int|null
     */
    public function getDriverId(): ?int
    {
        return $this->entity?->getVehicle()?->getDriver()?->getId();
    }

    /**
     * @return null
     */
    public function getEntityId()
    {
        return $this->entity?->getDigitalForm()?->getId();
    }

    /**
     * @return int|null
     */
    public function getEntityTeamId(): ?int
    {
        return $this->entity?->getVehicle()?->getTeam()->getId();
    }

    /**
     * Team, who changed this user
     * @return int|null
     */
    public function getTeamBy(): ?int
    {
        return $this->currentUser?->getTeam()->getId();
    }

    /**
     * User id, who changed this user
     * @return int|null
     */
    public function getUserBy(): ?int
    {
        return $this->currentUser?->getId();
    }
}
