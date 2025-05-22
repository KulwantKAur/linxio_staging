<?php

namespace App\Service\Route;

use App\Entity\Route;
use App\Entity\Tracker\TrackerHistory;
use App\Util\GeoHelper;
use Carbon\Carbon;
use Doctrine\Common\Collections\Criteria;

trait RouteHelperTrait
{
    public function getStartedRouteWithPartialRoute(
        Route $route,
        $dateFrom,
        $include = [],
        $withLastCoordinates = false,
        $order = Criteria::ASC,
        array $optimization = []
    ): ?array {
        $dateFrom = $dateFrom ? self::parseDateToUTC($dateFrom) : Carbon::now();

        if ($order === Criteria::ASC) {
            $previousRoute = $this->em->getRepository(Route::class)->getPreviousRoute($route);
        } else {
            $previousRoute = $this->em->getRepository(Route::class)->getNextRoute($route);
        }

        $previousRoute = $previousRoute && ($previousRoute->getFinishedAt() >= $dateFrom) ? $previousRoute : null;

        if ($previousRoute) {
            $tempRoute = $this->makePartialStartRoute(
                $previousRoute, $dateFrom, $include, $withLastCoordinates, $optimization
            );
        }

        return $tempRoute ?? null;
    }

    public function getFinishedRouteWithPartialRoute(
        Route $route,
        $dateTo,
        $include = [],
        $withLastCoordinates = false,
        $order = Criteria::ASC,
        array $optimization = []
    ): ?array {
        $dateTo = $dateTo ? self::parseDateToUTC($dateTo) : Carbon::now();

        if ($order === Criteria::ASC) {
            $nextRoute = $this->em->getRepository(Route::class)->getNextRoute($route);
        } else {
            $nextRoute = $this->em->getRepository(Route::class)->getPreviousRoute($route);
        }

        $nextRoute = $nextRoute && ($nextRoute->getStartedAt() <= $dateTo) ? $nextRoute : null;

        if ($nextRoute) {
            $tempRoute = $this->makePartialFinishRoute(
                $nextRoute, $dateTo, $include, $withLastCoordinates, $optimization
            );
        }

        return $tempRoute ?? null;
    }

    private function makePartialStartRoute(
        Route $route,
        \DateTime $dateFrom,
        ?array $include,
        ?bool $withLastCoordinates,
        array $optimization = []
    ): ?array {
        $coordinatesNeeded = [];
        $this->setCoordinatesToRoute($route, $include, $withLastCoordinates, $optimization);

        if ($route->getCoordinates()) {
            /** @var TrackerHistory $coordinate */
            foreach ($route->getCoordinates() as $coordinate) {
                if ((new \DateTime($coordinate['ts'])) >= $dateFrom) {
                    $coordinatesNeeded[] = $coordinate;
                }
            }
        }

        if (empty($coordinatesNeeded)) {
            return null;
        }

        $tempRoute = $this->getTempRouteEntity($route, $coordinatesNeeded, $dateFrom, $include);
        $tempRoute[RouteService::ORIGINAL_ROUTE_NAME] = $route->toArray(array_diff($include, ['coordinates']));

        return $tempRoute;
    }

    private function makePartialFinishRoute(
        Route $route,
        \DateTime $dateTo,
        ?array $include,
        ?bool $withLastCoordinates,
        array $optimization = []
    ): ?array {
        $coordinatesNeeded = [];
        $this->setCoordinatesToRoute($route, $include, $withLastCoordinates, $optimization);

        if ($route->getCoordinates()) {
            /** @var TrackerHistory $coordinate */
            foreach ($route->getCoordinates() as $coordinate) {
                if ((new \DateTime($coordinate['ts'])) <= $dateTo) {
                    $coordinatesNeeded[] = $coordinate;
                }
            }
        }

        if (empty($coordinatesNeeded)) {
            return null;
        }

        $tempRoute = $this->getTempFinishRouteEntity($route, $coordinatesNeeded, $dateTo, $include);
        $tempRoute[RouteService::ORIGINAL_ROUTE_NAME] = $route->toArray(array_diff($include, ['coordinates']));

        return $tempRoute;
    }

    private function makePartialFinishRouteFromArray(array $route, \DateTime $dateTo)
    {
        $coordinatesNeeded = [];
        $route[RouteService::ORIGINAL_ROUTE_NAME] = $route[RouteService::ORIGINAL_ROUTE_NAME] ?? $route;

        if (isset($route['coordinates']) && $route['coordinates']) {
            /** @var TrackerHistory $coordinate */
            foreach ($route['coordinates'] as $coordinate) {
                if ((new \DateTime($coordinate['ts'])) <= $dateTo) {
                    $coordinatesNeeded[] = $coordinate;
                }
            }
        }

        $route['coordinates'] = $coordinatesNeeded;
        $routePointFinishTs = new Carbon($route['pointFinish']['lastCoordinates']['ts']);

        if ($routePointFinishTs > $dateTo) {
            if ($route['coordinates']) {
                $route['pointFinish']['lastCoordinates']['ts'] = end($route['coordinates'])['ts'];
            }

            $route['duration'] = (new Carbon($route['pointFinish']['lastCoordinates']['ts']))
                ->diffInSeconds((new Carbon($route['pointStart']['lastCoordinates']['ts'])));
        }

        return $route;
    }

    /**
     * @param Route $route
     * @param array $coordinatesNeeded
     * @param $dateFrom
     * @return array
     */
    private function getPointWithValidCoordinates(Route $route, array $coordinatesNeeded, $dateFrom): array
    {
        $hasValidCoordinates = false;
        $startPoint = null;
        $firstCoordinateId = null;

        foreach ($coordinatesNeeded as $key => $coordinateDatum) {
            if ($key == 0) {
                $firstCoordinateId = $coordinateDatum['id'];
            }
            if (GeoHelper::hasCoordinatesWithCorrectValue($coordinateDatum['lat'], $coordinateDatum['lng'])) {
                $hasValidCoordinates = true;
                $startPoint = $this->em->getRepository(TrackerHistory::class)->find($coordinateDatum['id']);
                break;
            }
        }

        if (!$startPoint) {
            $startPoint = $this->em->getRepository(TrackerHistory::class)
                ->getLastTrackerHistoryWithCoordinatesByRoute($route, $dateFrom);
        }
        if (!$hasValidCoordinates && $startPoint) {
            if ($firstCoordinateId) {
                $startPointByCoordinates = $this->em->getRepository(TrackerHistory::class)->find($firstCoordinateId);

                if ($startPointByCoordinates) {
                    $startPointByCoordinates->setLat($startPoint->getLat());
                    $startPointByCoordinates->setLng($startPoint->getLng());
                    $startPoint = $startPointByCoordinates;
                }
            }

            foreach ($coordinatesNeeded as &$coordinateDatum2) {
                $coordinateDatum2['lat'] = $startPoint->getLat();
                $coordinateDatum2['lng'] = $startPoint->getLng();
            }
        }

        return ['startPoint' => $startPoint, 'coordinatesNeeded' => $coordinatesNeeded];
    }

    /**
     * It doesn't need to be saved in db!
     *
     * @param Route $route
     * @param array $coordinatesNeeded
     * @param $dateFrom
     * @param array $include
     * @return array
     * @throws \Exception
     */
    private function getTempRouteEntity(Route $route, array $coordinatesNeeded, $dateFrom, $include = []): array
    {
        $tempRoute = [];

        if (!empty($coordinatesNeeded)) {
            $startPoint = $this->getPointWithValidCoordinates($route, $coordinatesNeeded, $dateFrom);
            extract($startPoint);

            if (!$startPoint) {
                return [];
            }

            $tempRoute = new Route();
            $tempRoute->setEntityManager($this->em);
            $tempRoute->setDevice($route->getDevice());
            $tempRoute->setPointStart($startPoint);
            $tempRoute->setStartedAt($startPoint->getTs());
            $tempRoute->setStartCoordinates([
                'lat' => $startPoint->getLat(),
                'lng' => $startPoint->getLng()
            ]);
            $tempRoute->setFinishCoordinates([
                'lat' => $route->getPointFinish()->getLat() ?: $startPoint->getLat(),
                'lng' => $route->getPointFinish()->getLng() ?: $startPoint->getLng()
            ]);
            $tempRoute->setPointFinish($route->getPointFinish());
            $tempRoute->setFinishedAt(end($coordinatesNeeded)['ts']);
            $tempRoute->setType($route->getType());
            $tempRoute->setDriver($route->getDriver());
            $tempRoute->setVehicle($route->getVehicle());
            $tempRoute->setCoordinates($coordinatesNeeded);
            $tempRoute->setAddress($route->getAddress());
            $tempRoute->setDistance($route->getDistance());
        } elseif (!$include || !in_array('coordinates', $include, true)) {
            $trackerHistory = $this->em->getRepository(TrackerHistory::class)
                ->getLastCoordinatesByRoute($route, $dateFrom);

            if ($trackerHistory) {
                $tempRoute = new Route();
                $tempRoute->setEntityManager($this->em);
                $tempRoute->setDevice($route->getDevice());
                $tempRoute->setPointStart($trackerHistory);
                $tempRoute->setStartedAt($trackerHistory->getTs());
                $tempRoute->setPointFinish($route->getPointFinish());
                $tempRoute->setFinishedAt($route->getPointFinish()->getTs());
                $tempRoute->setStartCoordinates([
                    'lat' => $trackerHistory->getLat(),
                    'lng' => $trackerHistory->getLng()
                ]);
                $tempRoute->setFinishCoordinates([
                    'lat' => $route->getPointFinish()->getLat(),
                    'lng' => $route->getPointFinish()->getLng()
                ]);
                $tempRoute->setType($route->getType());
                $tempRoute->setDriver($route->getDriver());
                $tempRoute->setVehicle($route->getVehicle());
                $tempRoute->setCoordinatesFromTrackerHistory($trackerHistory);
                $tempRoute->setAddress($route->getAddress());
                $tempRoute->setDistance($route->getDistance());
            }
        }

        return $tempRoute ? $tempRoute->toArray($include) : [];
    }

    private function getTempFinishRouteEntity(Route $route, array $coordinatesNeeded, $dateTo, $include = [])
    {
        $tempRoute = null;

        if (!empty($coordinatesNeeded)) {
            $finishPoint = $this->em->getRepository(TrackerHistory::class)->find(end($coordinatesNeeded)['id']);
            $tempRoute = new Route();
            $tempRoute->setEntityManager($this->em);
            $tempRoute->setDevice($route->getDevice());
            $tempRoute->setPointStart($route->getPointStart());
            $tempRoute->setStartedAt($coordinatesNeeded[0]['ts']);
            $tempRoute->setPointFinish($finishPoint);
            $tempRoute->setFinishedAt($finishPoint->getTs());
            $tempRoute->setType($route->getType());
            $tempRoute->setDriver($route->getDriver());
            $tempRoute->setVehicle($route->getVehicle());
            $tempRoute->setCoordinates($coordinatesNeeded);
            $tempRoute->setAddress($route->getAddress());
        } elseif (!$include || !in_array('coordinates', $include, true)) {
            $trackerHistory = $this->em->getRepository(TrackerHistory::class)->getLastTHByRouteDateTo($route, $dateTo);

            if ($trackerHistory) {
                $tempRoute = new Route();
                $tempRoute->setEntityManager($this->em);
                $tempRoute->setDevice($route->getDevice());
                $tempRoute->setPointStart($route->getPointStart());
                $tempRoute->setStartedAt($route->getPointStart()->getTs());
                $tempRoute->setPointFinish($trackerHistory);
                $tempRoute->setFinishedAt($trackerHistory->getTs());
                $tempRoute->setType($route->getType());
                $tempRoute->setDriver($route->getDriver());
                $tempRoute->setVehicle($route->getVehicle());
                $tempRoute->setCoordinatesFromTrackerHistory($trackerHistory);
                $tempRoute->setAddress($route->getAddress());
            }
        }

        return $tempRoute ? $tempRoute->toArray($include) : null;
    }

    /**
     * @param array $routes
     * @param string $order
     * @param array $include
     * @return array|null
     */
    private function getStartStopRoute(array $routes, $order = Criteria::ASC, $include = [])
    {
        /** @var Route $firstRoute */
        $firstRoute = $routes[0];

        if ($firstRoute->getType() == Route::TYPE_DRIVING) {
            if ($order === Criteria::ASC) {
                $lastStoppedRoute = $this->em->getRepository(Route::class)->getPreviousRoute($firstRoute);
            } else {
                $lastStoppedRoute = $this->em->getRepository(Route::class)->getNextRoute($firstRoute);
            }

            if ($lastStoppedRoute && $lastStoppedRoute->getType() == Route::TYPE_STOP) {
                return $lastStoppedRoute->toArray($include);
            }
        }

        return null;
    }

    /**
     * @param array $routes
     * @param string $order
     * @param array $include
     * @return array|null
     */
    private function getFinishStopRoute(array $routes, $order = Criteria::ASC, $include = [])
    {
        /** @var Route $lastRoute */
        $lastRoute = end($routes);

        if ($lastRoute->getType() == Route::TYPE_DRIVING) {
            if ($order === Criteria::ASC) {
                $nextStoppedRoute = $this->em->getRepository(Route::class)->getNextRoute($lastRoute);
            } else {
                $nextStoppedRoute = $this->em->getRepository(Route::class)->getPreviousRoute($lastRoute);
            }

            if ($nextStoppedRoute && $nextStoppedRoute->getType() == Route::TYPE_STOP) {
                return $nextStoppedRoute->toArray($include);
            }
        }

        return null;
    }
}