<?php

namespace App\EventListener\Route;

use App\Events\Route\RouteUpdatedEvent;
use App\Service\Device\DeviceService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RouteListener implements EventSubscriberInterface
{
    private $listenedEvents = [];

    private $em;
    private $tokenStorage;
    private $deviceService;

    /**
     * RouteListener constructor.
     * @param EntityManager $em
     * @param TokenStorageInterface $tokenStorage
     * @param DeviceService $deviceService
     */
    public function __construct(
        EntityManager $em,
        TokenStorageInterface $tokenStorage,
        DeviceService $deviceService
    ) {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->deviceService = $deviceService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            RouteUpdatedEvent::NAME => 'onRouteUpdated'
        ];
    }

    /**
     * @param string $event
     * @return bool
     */
    protected function isEventWas(string $event): bool
    {
        return in_array($event, $this->listenedEvents, true);
    }

    /**
     * @param RouteUpdatedEvent $event
     */
    public function onRouteUpdated(RouteUpdatedEvent $event)
    {
        // todo keep for further development
    }
}