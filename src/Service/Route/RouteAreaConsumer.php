<?php

namespace App\Service\Route;

use App\Command\Traits\CommandLoggerTrait;
use App\Entity\Area;
use App\Entity\Route;
use App\Entity\RouteFinishArea;
use App\Entity\RouteStartArea;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class RouteAreaConsumer implements ConsumerInterface
{
    use CommandLoggerTrait;

    private $em;
    private $logger;
    private $routeService;

    public const QUEUES_NUMBER = 3; // should be equal to number of queues in `config/rabbitmq.yaml`
    public const ROUTING_KEY_PREFIX = 'route_area_'; // should be equal to `routing_keys` of queues

    public function __construct(
        EntityManager $em,
        LoggerInterface $logger,
        RouteService $routeService
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->routeService = $routeService;
    }

    public function execute(AMQPMessage $msg)
    {
        $message = json_decode($msg->getBody());

        try {
            if (!$message) {
                return;
            }

            $routeId = $message->route_id;

            $route = $this->em->getRepository(Route::class)->find($routeId);

            if (!$route) {
                return;
            }

//            $area = $this->em->getRepository(Area::class)->count([
//                'team' => $route->getDevice()->getTeam(),
//                'status' => Area::STATUS_ACTIVE
//            ]);
//
//            if (!$area) {
//                return;
//            }

            if ($route->getPointStart()?->getLng() && $route->getPointStart()?->getLat()) {
                $startAreas = $this->em->getRepository(Area::class)->findByPoint(
                    $route->getPointStart()->getLng() . ' ' . $route->getPointStart()->getLat(),
                    $route->getDevice()->getTeam()
                );
            }

            if ($route->getPointFinish()?->getLng() && $route->getPointFinish()?->getLat()) {
                $finishAreas = $this->em->getRepository(Area::class)->findByPoint(
                    $route->getPointFinish()->getLng() . ' ' . $route->getPointFinish()->getLat(),
                    $route->getDevice()->getTeam()
                );
            }
//            $this->logger->error('route id: ' . $route->getId() . 'area count: ' . count($startAreas ?? []) . ' ' . count($finishAreas ?? []));

            if ($startAreas ?? null) {
                foreach ($startAreas as $startArea) {
                    if (!$route->checkStartArea($startArea)) {
                        $routeStartArea = new RouteStartArea(['area' => $startArea, 'route' => $route]);
                        $this->em->persist($routeStartArea);
                    }
                }
            } else {
                $route->removeStartAreas();
            }

            if ($finishAreas ?? null) {
                foreach ($finishAreas as $finishArea) {
                    if (!$route->checkFinishArea($finishArea)) {
                        $routeFinishArea = new RouteFinishArea(['area' => $finishArea, 'route' => $route]);
                        $this->em->persist($routeFinishArea);
                    }
                }
            } else {
                $route->removeFinishAreas();
            }

            $this->em->flush();
            $this->em->clear();
        } catch (\Exception $exception) {
            $this->logger->error(ExceptionHelper::convertToJson($exception));
            $this->logException($exception);
        }
    }
}
