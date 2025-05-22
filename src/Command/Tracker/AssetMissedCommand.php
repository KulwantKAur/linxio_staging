<?php

namespace App\Command\Tracker;

use App\Entity\Asset;
use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Command\Traits\BreakableTrait;

#[AsCommand(name: 'app:tracker:asset-missed')]
class AssetMissedCommand extends Command
{
    use LockableTrait;
    use BreakableTrait;

    private $em;
    private $notificationDispatcher;

    protected function configure(): void
    {
        $this->setDescription('Update sensor status');
    }

    /**
     * UpdateStatusCommand constructor.
     * @param EntityManager $em
     * @param NotificationEventDispatcher $notificationDispatcher
     */
    public function __construct(
        EntityManager $em,
        NotificationEventDispatcher $notificationDispatcher
    ) {
        $this->em = $em;
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
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        /** @var Event $eventMissed */
        $eventMissed = $this->em->getRepository(Event::class)->findOneBy(['name' => Event::ASSET_MISSED]);

        $assets = $this->em->getRepository(Asset::class)->findAll();

        $progressBar = new ProgressBar($output, count($assets));
        $progressBar->start();

        /** @var Asset $asset */
        foreach ($assets as $asset) {
            try {
                if ($asset->getDuration()) {
                    $eventLogs = $this->em->getRepository(EventLog::class)->findEventLogByDetailsJson(
                        "->> 'lastOccurredAt' = '" . $asset->getLastOccurredAtFormatted() . "' AND details->> 'id' = '" . $asset->getId() . "'",
                        $eventMissed->getId()
                    );

                    if (count($eventLogs)) {
                        continue;
                    }

                    $notifications = $this->em->getRepository(Notification::class)->getTeamNotifications(
                        $eventMissed,
                        $asset->getTeam(),
                        new \DateTime(),
                        $asset,
                        [],
                        $asset->getLastOccurredAt()
                    );

                    if (!$notifications) {
                        return 0;
                    }

                    $this->notificationDispatcher->dispatch($eventMissed->getName(), $asset, new \DateTime());
                }
            } catch (\Exception $exception) {
                $output->writeln(
                    PHP_EOL . sprintf(
                        'Error with assetId: %s with message: %s',
                        $asset->getId(),
                        $exception->getMessage()
                    )
                );
            }

            $progressBar->advance();
        }

        $this->em->clear();

        $progressBar->finish();
        $output->writeln(PHP_EOL . 'Sensor status successfully updated!');

        return 0;
    }
}