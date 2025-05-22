<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-partition',
    description: 'Create partition for tracker_history table',
)]
class CreatePartitionCommand extends Command
{
    public function __construct(private Connection $connection)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<SQL
        DO $$
        DECLARE
            start_date DATE := date_trunc('month', CURRENT_DATE + interval '1 month');
            end_date DATE := start_date + interval '1 month';
            partition_name TEXT := 'tracker_history_' || to_char(start_date, 'YYYY_MM');
        BEGIN
            EXECUTE format('
                CREATE TABLE IF NOT EXISTS %I PARTITION OF tracker_history
                FOR VALUES FROM (%L) TO (%L);
            ', partition_name, start_date, end_date);
        END $$;
        SQL;
        
                $this->connection->executeStatement($sql);
                $output->writeln('Partition created if it did not exist.');
        
                return Command::SUCCESS;
    }
}
