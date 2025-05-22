<?php

namespace App\Tests\Behat\Context\Traits;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

trait RabbitMqTrait
{
    /**
     * @param string $prefixQueue
     * @param string $producerName
     * @param string $specialQueName
     *
     * @return array
     */
    private function getQueuedMessages($prefixQueue, $producerName, $specialQueName = '')
    {
        $channel = $this->getChannel($producerName);
        $queue = $specialQueName ?? $this->getQueueName($prefixQueue, $producerName);

        $queuedMessages = [];
        do {
            /** @var AMQPMessage $message */
            $message = $channel->basic_get($queue);

            if (!$message instanceof AMQPMessage) {
                break;
            }

            $queuedMessages[] = $message->getBody();

            if ($message->get('message_count') == 0) {
                break;
            }
        } while (true);

        return $queuedMessages;
    }

    /**
     * @param string $producerName
     *
     * @return AMQPChannel
     */
    private function getChannel($producerName): AMQPChannel
    {
        $container = $this->getKernel()->getContainer();

        $producerService = sprintf('old_sound_rabbit_mq.%s_producer', $producerName);
        $producer = $container->get($producerService);

        return $producer->getChannel();
    }

    /**
     * @param string $prefixQueue
     * @param string $producerName
     *
     * @return string
     */
    private function getQueueName($prefixQueue, $producerName)
    {
        return sprintf('%s.%s', $prefixQueue, $producerName);
    }

    /**
     * @param $queue
     * @param $producer
     * @When the queue associated to :queue :producer producer is empty
     */
    public function theQueueAssociatedToProducerIsEmpty($queue, $producer)
    {
        $channel = $this->getChannel($producer);
        $channel->queue_declare($queue, false, true, false, false);
        $channel->queue_purge($queue);

        if ($channel->basic_get($queue)) {
            throw new \LogicException(sprintf('The queue %s does not seem to be empty.', $queue));
        }
    }
}