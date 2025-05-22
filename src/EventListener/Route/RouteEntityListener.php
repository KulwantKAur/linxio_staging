<?php

namespace App\EventListener\Route;

use App\Entity\Route;
use App\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class RouteEntityListener
{
    private EntityManagerInterface $em;

    /**
     * @param Route $route
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Route $route, PreUpdateEventArgs $event)
    {
        if ($event->hasChangedField('pointStart') || $event->hasChangedField('pointFinish')) {
            $this->em = $event->getEntityManager();
            $this->updateOdometer($route);
            $this->updateDistanceStats($route);
            $this->updateCoordinates($route);
        }
    }

    /**
     * @param Route $route
     * @return Route
     */
    private function updateDistanceStats(Route $route): Route
    {
        if ($route->getStartOdometer() && $route->getFinishOdometer()) {
            $route->setDistance($route->getFinishOdometer() - $route->getStartOdometer());
        }

        return $route;
    }

    /**
     * @param Route $route
     * @return Route
     */
    private function updateOdometer(Route $route): Route
    {
        $this->initEmptyOdometerValues($route);

        if ($route->getPointFinish() && $route->getPointFinish()->getOdometer()) {
            $route->setFinishOdometer($route->getPointFinish()->getOdometer());
        }
        if ($route->getPointStart() && $route->getPointStart()->getOdometer()) {
            $route->setStartOdometer($route->getPointStart()->getOdometer());
        }

        return $route;
    }

    /**
     * @param Route $route
     * @return Route
     */
    private function initEmptyOdometerValues(Route $route): Route
    {
        if ($route->isRouteWithoutOdometer()) {
            $prevRouteWithOdometer = $this->em->getRepository(Route::class)->getPreviousRoute($route);
            $prevRouteWithOdometer = $prevRouteWithOdometer && $prevRouteWithOdometer->getFinalOdometer()
                ? $prevRouteWithOdometer
                : $this->em->getRepository(Route::class)->getPreviousRouteWithOdometer($route);
            $prevOdometer = $prevRouteWithOdometer?->getFinalOdometer();

            if ($prevOdometer) {
                $route->setStartOdometer($prevOdometer);

                if ($route->getPointFinish()) {
                    $route->setFinishOdometer($prevOdometer);
                }
            }
        }

        return $route;
    }

    /**
     * @param Route $route
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function updateCoordinates(Route $route)
    {
        if ($route->getPointFinish()) {
            $point = $route->getPointFinish()->getDevice()->getValidFuturePoint($route->getPointFinish());

            if (!$point) {
                $point = $route->getPointFinish()->getDevice()->getValidPreviousPoint($route->getPointFinish());
            }

            if ($point) {
                $route->setFinishCoordinates([
                    'lat' => $point->getLat(),
                    'lng' => $point->getLng()
                ]);
            }
        }

        if ($route->getPointStart()) {
            $point = $route->getPointStart()->getDevice()->getValidPreviousPoint($route->getPointStart());

            if (!$point) {
                $point = $route->getPointStart()->getDevice()->getValidFuturePoint($route->getPointStart());
            }

            if ($point) {
                $route->setStartCoordinates([
                    'lat' => $point->getLat(),
                    'lng' => $point->getLng()
                ]);
            }
        }
    }

    public function postLoad(Route $route, LifecycleEventArgs $args)
    {
        $route->setEntityManager($args->getObjectManager());
    }

    public function postPersist(Route $route, PostPersistEventArgs $args)
    {
        $tripCodeSettingValue = $route->getDevice()?->getTeam()?->getSettingsByName(Setting::TRIP_CODE)?->getValue();
        if ($tripCodeSettingValue) {
            $route->setTripCode(Route::ADMIN_NEPT);
        }
    }
}