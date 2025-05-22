<?php

namespace App\Command;

use App\Entity\User;
use App\Service\Chat\CentrifugoService;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:user:update-network-status')]
class UpdateUserNetworkStatusCommand extends Command
{
    private const BATCH_SIZE = 50;

    private $em;
    private $chatService;
    private $userService;

    protected function configure(): void
    {
        $this->setDescription('Update users network status');
    }

    /**
     * @param EntityManager $em
     * @param CentrifugoService $chatService
     * @param UserService $userService
     */
    public function __construct(EntityManager $em, CentrifugoService $chatService, UserService $userService)
    {
        $this->em = $em;
        $this->chatService = $chatService;
        $this->userService = $userService;
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
        $users = $this->em->getRepository(User::class)->getAllToUpdateNetworkStatusQuery();
        $usersCount = $this->em->getRepository(User::class)->getAllToUpdateNetworkStatusCount();
        $progressBar = new ProgressBar($output, $usersCount);
        $progressBar->start();

        foreach ($users->toIterable() as $user) {
            $user->setNetworkStatus(User::NETWORK_STATUS_OFFLINE);
            $this->chatService->notifyTeamUserStatusUpdated($user);

            if (($counter % self::BATCH_SIZE) === 0) {
                $this->em->flush();
                $this->em->clear();
            }

            ++$counter;
            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln(PHP_EOL . 'Users network status updated!');
        $this->em->flush();
        $this->em->clear();

        return 0;
    }
}