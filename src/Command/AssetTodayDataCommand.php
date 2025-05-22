<?php

namespace App\Command;

use App\Command\Traits\CommandLoggerTrait;
use App\Command\Traits\ProcessableTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Asset;
use App\Service\Asset\AssetService;
use App\Service\Redis\MemoryDbService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:asset-today-data')]
class AssetTodayDataCommand extends Command
{
    use CommandLoggerTrait;
    use RedisLockTrait;
    use ProcessableTrait;

    protected function configure(): void
    {
        $this->setDescription('Calculate asset today data');
        $this->addOption('teamId', null, InputOption::VALUE_OPTIONAL);
        $this->updateConfigWithProcessOptions();
    }

    public function __construct(
        private readonly EntityManager $em,
        private readonly MemoryDbService $memoryDbService,
        private readonly AssetService $assetService,
        private readonly LoggerInterface $logger,
        private readonly ParameterBagInterface $params
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lock = $this->getLock($this->getProcessName($input));

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        try {
            $assets = $this->em->getRepository(Asset::class)->getAssetsWithNewData((new Carbon())->subDay());
            $output->writeln('Asset count: ' . count($assets));
            /** @var Asset $asset */
            foreach ($assets as $asset) {
                $output->writeln('Asset id: ' . $asset->getId());

                $assetTs = $asset->getLastOccurredAt()?->getTimestamp();
                $timezone = $asset->getClient()?->getTimeZoneName();
                $now = Carbon::createFromTimestamp(time(), $timezone);
                $ts = $assetTs ? Carbon::createFromTimestamp($assetTs, $timezone) : null;

                $assetData = $this->memoryDbService->get($asset->getTodayDataKey());
                $lastCheckDate = isset($assetData['lastCheck'])
                    ? Carbon::createFromTimestamp($assetData['lastCheck'], $timezone) : null;


                if ($lastCheckDate && $lastCheckDate->day !== $now->day) {
                    $this->memoryDbService->set($asset->getTodayDataKey(), ['data' => null, 'lastCheck' => time()]);
                }

                if (!$assetTs) {
                    continue;
                }
                if ($assetData && isset($assetData['lastCheck']) && $assetData['lastCheck'] > $assetTs) {
                    continue;
                }

                $todayData = $this->assetService->getDailyData($asset, $asset->getClient()?->getTimeZoneName());
                $this->memoryDbService->set($asset->getTodayDataKey(), ['data' => $todayData, 'lastCheck' => time()]);
            }

            return 0;
        } catch (\Throwable $e) {
            $this->logException($e);
        }

        return 0;
    }
}