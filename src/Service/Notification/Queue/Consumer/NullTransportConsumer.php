<?php


namespace App\Service\Notification\Queue\Consumer;


use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class NullTransportConsumer implements ConsumerInterface
{
    public function execute(AMQPMessage $msg): void
    {
    }
}