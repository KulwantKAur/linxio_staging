<?php

namespace App\Command\Tracker;

use App\Command\Traits\BreakableTrait;
use App\Command\Traits\DevicebleTrait;
use App\Command\Traits\MemorableTrait;
use App\Command\Traits\ProcessableTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Device;
use App\Entity\Route;
use App\Service\Route\RouteService;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:tracker:update-routes-postponed-data')]
class UpdateRoutesPostponedDataCommand extends Command
{
    use BreakableTrait;
    use RedisLockTrait;
    use MemorableTrait;
    use DevicebleTrait;
    use ProcessableTrait;

    private const BATCH_SIZE = 20;

    private $em;
    private $routeService;
    private $logger;
    private $params;

    protected function configure(): void
    {
        $this->setDescription('Update postponed data for routes');
        $this->getDefinition()->addOptions([
            new InputOption('createdAtFrom', null, InputOption::VALUE_OPTIONAL, 'createdAtFrom', null),
            new InputOption('createdAtTo', null, InputOption::VALUE_OPTIONAL, 'createdAtTo', null),
            new InputOption('startedAtFrom', null, InputOption::VALUE_OPTIONAL, 'startedAtFrom', null),
            new InputOption('startedAtTo', null, InputOption::VALUE_OPTIONAL, 'startedAtTo', null),
            new InputOption('startDeviceId', null, InputOption::VALUE_OPTIONAL, 'startDeviceId', null),
            new InputOption('finishDeviceId', null, InputOption::VALUE_OPTIONAL, 'finishDeviceId', null),
        ]);
        $this->updateConfigWithDeviceOptions();
    }

    /**
     * UpdateWrongRoutesStatsCommand constructor.
     * @param EntityManager $em
     * @param RouteService $routeService
     * @param LoggerInterface $logger
     * @param ParameterBagInterface $params
     */
    public function __construct(
        EntityManager $em,
        RouteService $routeService,
        LoggerInterface $logger,
        ParameterBagInterface $params
    ) {
        $this->em = $em;
        $this->routeService = $routeService;
        $this->logger = $logger;
        $this->params = $params;

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
        // @todo remove if it's ok with AWS cron
//        $this->setMemoryLimit($this->params->get('calculating_job_memory'));
//        $this->breakScriptByTTL($this->params->get('calculating_job_ttl'));
//        $lock = $this->getLock($this->getName());
        $counter = 0;
        $createdAtFrom = $input->getOption('createdAtFrom');
        $createdAtTo = $input->getOption('createdAtTo');
        $startedAtFrom = $input->getOption('startedAtFrom');
        $startedAtTo = $input->getOption('startedAtTo');
        $startDeviceId = $input->getOption('startDeviceId');
        $finishDeviceId = $input->getOption('finishDeviceId');
        $deviceIds = $this->getDeviceIdsByInput($input) ?: $this->em->getRepository(Device::class)
            ->getDeviceIds(null, null, true, $startDeviceId, $finishDeviceId);

//        if (!$lock->acquire()) {
//            $output->writeln('The command is already running in another process.');
//
//            return 0;
//        }

        try {
            $progressBar = new ProgressBar($output, count($deviceIds));
            $progressBar->start();

            foreach ($deviceIds as $deviceId) {
                $output->writeln('Device ID: ' . $deviceId);
                $routesQuery = $this->em->getRepository(Route::class)->getRoutesForPostponedDataQuery(
                    $deviceId,
                    $createdAtFrom,
                    $createdAtTo,
                    $startedAtFrom,
                    $startedAtTo
                );

                foreach ($routesQuery->toIterable() as $route) {
                    $this->routeService->updateRoutePostponedData($route);

                    if (($counter % self::BATCH_SIZE) === 0) {
                        $this->em->flush();
                        $this->em->clear();
                    }

                    ++$counter;
                }

                $progressBar->advance();
            }

            $this->em->flush();
            $this->em->clear();
            $progressBar->finish();
            $output->writeln(PHP_EOL . 'Postponed data for routes successfully updated!');
        } catch (\Exception $exception) {
            $this->logger->error(ExceptionHelper::convertToJson($exception), [$this->getName()]);
            $output->writeln(PHP_EOL . $exception->getMessage());
        }

        $this->release();

        return 0;
    }
}
