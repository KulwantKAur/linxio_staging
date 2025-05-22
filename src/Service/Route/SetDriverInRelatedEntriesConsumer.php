<?php

namespace App\Service\Route;

use App\Entity\DriverHistory;
use App\Service\Route\Driver\SetDriverExecutorRegistryInterface;
use Doctrine\ORM\EntityManager;
use Exception;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class SetDriverInRelatedEntriesConsumer implements ConsumerInterface
{
    private $em;
    private $executorRegistry;
    private $logger;

    /**
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param SetDriverExecutorRegistryInterface $executorRegistry
     */
    public function __construct(
        EntityManager $em,
        SetDriverExecutorRegistryInterface $executorRegistry,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->executorRegistry = $executorRegistry;
        $this->logger = $logger;
    }

    public function execute(AMQPMessage $msg): bool
    {
        /** @var DriverHistory $driverHistory */
        $driverHistoryId = $msg->getBody();
        try {
            $this->em->beginTransaction();

            foreach ($this->executorRegistry->getExecutors() as $executor) {
                $executor->execute($driverHistoryId);
            }

            $this->em->commit();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return false;
        }

        return true;
    }
}
