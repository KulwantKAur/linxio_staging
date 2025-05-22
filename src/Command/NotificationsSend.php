<?php

namespace App\Command;

use App\Command\Traits\ProcessableTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Notification\Message;
use App\Entity\Notification\Transport;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:notifications:send')]
class NotificationsSend extends Command
{
    use RedisLockTrait;
    use ProcessableTrait;

    private const BATCH_SIZE = 1000;

    private EntityManager $em;
    private ContainerInterface $container;
    private ParameterBagInterface $params;
    private LoggerInterface $logger;

    public function __construct(
        EntityManager $em,
        ContainerInterface $container,
        ParameterBagInterface $params,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->container = $container;
        $this->params = $params;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Send notifications');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lock = $this->getLock($this->getName());
        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        try {
            $output->writeln('Sending messages started');
            $counter = 0;

            $ntfList = $this->em->getRepository(Message::class)->findMessageToSend(true);
//            $ntfList = $this->getSlicedItemsByProcess(
//                $this->em->getRepository(Message::class)->findMessageToSend(true),
//                $input,
//                $output
//            );

//            $progressBar = new ProgressBar($output, count($ntfList));
//            $progressBar->start();

            foreach ($ntfList as [$ntfMessage]) {
                try {
                    /** @var Message $ntfMessage */
                    $output->writeln('Message id: ' . $ntfMessage->getId());

                    $this->getProducer($ntfMessage->getTransportType())->publish(
                        json_encode(
                            [
                                'id' => $ntfMessage->getId(),
                            ]
                        )
                    );
                    $ntfMessage
                        ->setStatus(Message::TYPE_DELIVERY)
                        ->setProcessingTime(new \DateTime());

                    if (($counter % self::BATCH_SIZE) === 0) {
                        $this->em->flush();
                        $this->em->clear();
                    }

                    ++$counter;
                } catch (\Exception $exception) {
                    $message = sprintf(
                        'Error with message id: %s with message: %s',
                        $ntfMessage->getId(),
                        $exception->getMessage()
                    );
                    $this->logger->error($message, [$this->getName()]);
                    $output->writeln(PHP_EOL . $message);
                }
//                $progressBar->advance();
            }
            $this->em->flush();
//            $progressBar->finish();

        } catch (\Exception $exception) {
            $this->logger->error(ExceptionHelper::convertToJson($exception), [$this->getName()]);
            $output->writeln(PHP_EOL . $exception->getMessage());
        }

        $this->release();

        $output->writeln('Sending messages finished');

        return 0;
    }

    protected function getProducer(string $transport): Producer
    {
        return match ($transport) {
            Transport::TRANSPORT_SMS => $this->container->get('old_sound_rabbit_mq.sms_producer'),
            Transport::TRANSPORT_EMAIL => $this->container->get('old_sound_rabbit_mq.email_producer'),
            Transport::TRANSPORT_WEB_APP => $this->container->get('old_sound_rabbit_mq.webapp_producer'),
            Transport::TRANSPORT_MOBILE_APP => $this->container->get('old_sound_rabbit_mq.mobileapp_producer'),
            default => throw new \Exception('Invalid transport'),
        };
    }
}
