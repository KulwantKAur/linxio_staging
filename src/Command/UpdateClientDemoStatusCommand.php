<?php

namespace App\Command;

use App\Entity\Client;
use App\Entity\Notification\Event;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:client-demo:update-status')]
class UpdateClientDemoStatusCommand extends Command
{
    private const BATCH_SIZE = 100;

    private $em;
    private $notificationDispatcher;

    /** @var \App\Repository\ClientRepository */
    private $clientRepo;

    protected function configure(): void
    {
        $this->setDescription('Update client demo status');
    }

    /**
     * UpdateClientDemoStatusCommand constructor.
     * @param EntityManager $em
     * @param NotificationEventDispatcher $notificationDispatcher
     */
    public function __construct(EntityManager $em, NotificationEventDispatcher $notificationDispatcher)
    {
        $this->em = $em;
        $this->clientRepo = $em->getRepository(Client::class);
        $this->notificationDispatcher = $notificationDispatcher;

        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $counter = 0;
        foreach ($this->clientRepo->updateClientDemoBlocked(true) as [$client]) {

            /** @var Client $client */
            $client
                ->setStatus(Client::STATUS_BLOCKED)
                ->setUpdatedAt(new \DateTime());
            $this->notificationDispatcher->dispatch(Event::CLIENT_DEMO_EXPIRED, $client);

            if (($counter % self::BATCH_SIZE) === 0) {
                $this->em->flush();
                $this->em->clear();
            }
            ++$counter;
        }

        $this->em->flush();

        return 0;
    }
}