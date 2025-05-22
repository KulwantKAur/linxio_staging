<?php

namespace App\Command;

use App\Command\Traits\CommandLoggerTrait;
use App\Command\Traits\RedisLockTrait;
use App\Service\Redis\MemoryDbService;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:test:command')]
class TestCommand extends Command
{
//    use RedisLockTrait;
    use CommandLoggerTrait;

    private $params;
    private $memoryDb;

    public function __construct(
        ParameterBagInterface $params,
        MemoryDbService $memoryDbService,
        private readonly Logger $logger
    ) {
        $this->params = $params;
        $this->memoryDb = $memoryDbService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('sleep', null, InputOption::VALUE_OPTIONAL, '', 10);
        $this->addOption('action', null, InputOption::VALUE_OPTIONAL, '');
        $this->addOption('key', null, InputOption::VALUE_OPTIONAL, '');
        $this->addOption('data', null, InputOption::VALUE_OPTIONAL, '');
        $this->setDescription('Test command');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            throw new \Exception('test exc', 500);
            $action = $input->getOption('action');
            $key = $input->getOption('key');
            $data = $input->getOption('data');
            if ($action === 'set') {
                $data = json_decode($data);
                $this->memoryDb->set($key, $data);
            } elseif ($action === 'get') {
                $output->writeln(json_encode($this->memoryDb->get($key)));
            }

//            $output->writeln('Test command started');
//
//            $this->memoryDb->set('test', 1, ['tag2']);
//
//            $output->writeln($this->memoryDb->get('test'));
//
//            $output->writeln('Test command finished');
        } catch (\Exception $exception) {
            $this->logException($exception);
            $output->writeln($exception->getTraceAsString());
            throw new \Exception('test exc', 500);
        }

        return 0;
    }
}
