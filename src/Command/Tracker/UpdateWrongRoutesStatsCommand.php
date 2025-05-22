<?php

namespace App\Command\Tracker;

use App\Command\Traits\BreakableTrait;
use App\Command\Traits\MemorableTrait;
use App\Command\Traits\ProcessableTrait;
use App\Command\Traits\RedisLockTrait;
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

#[AsCommand(name: 'app:tracker:update-wrong-routes-stats')]
class UpdateWrongRoutesStatsCommand extends Command
{
    use BreakableTrait;
    use RedisLockTrait;
    use MemorableTrait;
    use ProcessableTrait;

    private const BATCH_SIZE = 10;

    private $em;
    private $routeService;
    private $logger;
    private $params;

    protected function configure(): void
    {
        $this->setDescription('Update stats for wrong routes');
        $this->getDefinition()->addOptions([
            new InputOption('createdAtFrom', null, InputOption::VALUE_OPTIONAL, 'createdAtFrom', null),
            new InputOption('createdAtTo', null, InputOption::VALUE_OPTIONAL, 'createdAtTo', null),
            new InputOption('startedAtFrom', null, InputOption::VALUE_OPTIONAL, 'startedAtFrom', null),
            new InputOption('startedAtTo', null, InputOption::VALUE_OPTIONAL, 'startedAtTo', null),
            new InputOption('speedDiff', null, InputOption::VALUE_OPTIONAL, 'speedDiff', 1),
        ]);
    }

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
        $this->setMemoryLimit($this->params->get('calculating_job_memory'));
        $this->breakScriptByTTL($this->params->get('calculating_job_ttl'));
        $lock = $this->getLock($this->getName());
        $counter = 0;
        $createdAtFrom = $input->getOption('createdAtFrom');
        $createdAtTo = $input->getOption('createdAtTo');
        $startedAtFrom = $input->getOption('startedAtFrom');
        $startedAtTo = $input->getOption('startedAtTo');
        $speedDiff = $input->getOption('speedDiff');

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        try {
            $routesQuery = $this->em->getRepository(Route::class)->getRoutesWithWrongSpeedStatsQuery(
                $createdAtFrom,
                $createdAtTo,
                $startedAtFrom,
                $startedAtTo,
                $speedDiff
            );

            $progressBar = new ProgressBar($output, 1);
            $progressBar->start();

            foreach ($routesQuery->toIterable() as $route) {
                $this->routeService->updateRoutePostponedData($route);

                if (($counter % self::BATCH_SIZE) === 0) {
                    $this->em->flush();
                    $this->em->clear();
                }

                ++$counter;
            }

            $this->em->flush();
            $this->em->clear();
            $progressBar->advance();
            $progressBar->finish();
            $output->writeln(PHP_EOL . 'Stats for wrong routes successfully updated!');
        } catch (\Exception $exception) {
            $this->logger->error(ExceptionHelper::convertToJson($exception), [$this->getName()]);
            $output->writeln(PHP_EOL . $exception->getMessage());
        }

        $this->release();

        return 0;
    }
}
