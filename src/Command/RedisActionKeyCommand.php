<?php

namespace App\Command;

use App\Command\Traits\RedisLockTrait;
use App\Service\Redis\MemoryDbService;
use App\Service\Redis\RedisService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:redis:command')]
class RedisActionKeyCommand extends Command
{
//    use RedisLockTrait;
    private $params;
    private $redisService;

    public function __construct(ParameterBagInterface $params, RedisService $redisService)
    {
        $this->params = $params;
        $this->redisService = $redisService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('key', null, InputOption::VALUE_OPTIONAL, '');
        $this->addOption('action', null, InputOption::VALUE_OPTIONAL, '');
        $this->addOption('data', null, InputOption::VALUE_OPTIONAL, '');
        $this->setDescription('Redis command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $action = $input->getOption('action');
            $key = $input->getOption('key');
            $data = $input->getOption('data');
            if ($action === 'delete') {
                $output->writeln($this->redisService->deleteItem($key));
            } elseif ($action === 'get') {
                $output->writeln(json_encode($this->redisService->get($key)));
            } elseif ($action === 'set') {
                $data = json_decode($data);
                $this->redisService->set($key, $data);
            }
        } catch (\Exception $exception) {
            $output->writeln($exception->getTraceAsString());
        }

        return 0;
    }
}
