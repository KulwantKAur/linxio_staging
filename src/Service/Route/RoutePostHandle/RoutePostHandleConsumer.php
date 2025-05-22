<?php

namespace App\Service\Route\RoutePostHandle;

use App\Command\Traits\CommandLoggerTrait;
use App\Service\Route\RouteService;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class RoutePostHandleConsumer implements ConsumerInterface
{
    use CommandLoggerTrait;

    private $em;
    private $logger;
    private $routeService;

    public const QUEUES_NUMBER = 6; // should be equal to number of queues in `config/rabbitmq.yaml`
    public const ROUTING_KEY_PREFIX = 'routes_post_handle_device_'; // should be equal to `routing_keys` of queues

    /**
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param RouteService $routeService
     */
    public function __construct(
        EntityManager $em,
        LoggerInterface $logger,
        RouteService $routeService
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->routeService = $routeService;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function execute(AMQPMessage $msg)
    {
        $message = json_decode($msg->getBody());

        try {
            if (!$message) {
                return;
            }

            $deviceId = $message->device_id;
            $dateFrom = $message->date_from;
            $dateTo = $message->date_to;

            // @todo uncomment if it works good for recalculating
//            $this->routeService->updateRoutesWithWrongFinishPoints(
//                $deviceId,
//                $dateFrom,
//                $dateTo
//            );
            $this->routeService->updateRoutesPostponedData(
                $deviceId,
                $dateFrom,
                $dateTo
            );
            // @todo remove validation `isRouteValidToTriggerEvent()` in future of each method below
            $this->routeService->vehicleDGFormEvent(
                $deviceId,
                $dateFrom,
                $dateTo
            );

//            $this->routeService->vehicleStandingEvent(
//                $deviceId,
//                $dateFrom,
//                $dateTo
//            );
//            $this->routeService->vehicleDrivingEvent(
//                $deviceId,
//                $dateFrom,
//                $dateTo
//            );
//            $this->routeService->vehicleMovingEvent(
//                $deviceId,
//                $dateFrom,
//                $dateTo
//            );

            $this->em->clear();
            $this->em->getConnection()->close();
        } catch (\Exception $exception) {
            $this->logger->error(ExceptionHelper::convertToJson($exception));
            $this->logException($exception);
        }
    }
}
