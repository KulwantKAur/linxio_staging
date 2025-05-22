<?php

namespace App\EventListener\Area;

use App\Entity\Area;
use App\Entity\User;
use App\Enums\EntityHistoryTypes;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Container\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AreaEntityListener
{
    private $container;
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage, ContainerInterface $container)
    {
        $this->container = $container;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param Area $area
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Area $area, PreUpdateEventArgs $event)
    {
        $this->preUpdateProcess($area, $event);
    }

    /**
     * @param Area $area
     * @param PreUpdateEventArgs $event
     */
    private function preUpdateProcess(Area $area, PreUpdateEventArgs $event)
    {
        if ($event->hasChangedField('status')) {
            $em = $this->container->get('doctrine.orm.history_entity_manager');
            $entityHistoryService = $this->container->get("app.entity_history_service");
            $entityHistoryService->setEntityManager($em);
            $createdById = $this->tokenStorage->getToken() &&
            $this->tokenStorage->getToken()->getUser() instanceof User
                ? $this->tokenStorage->getToken()->getUser()->getId() : null;
            $entityHistoryService->create(
                $area,
                $area->getStatus(),
                EntityHistoryTypes::AREA_STATUS,
                null,
                $createdById
            );
        }
    }

    public function postLoad(Area $area, LifecycleEventArgs $args)
    {

    }
}