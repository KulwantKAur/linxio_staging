<?php

namespace App\Command\Traits;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

trait ProcessableTrait
{
    /**
     * @param InputInterface $input
     * @return int
     */
    private function getProcessNum(InputInterface $input): int
    {
        return intval($input->getOption('number') ?: 1);
    }

    /**
     * @param InputInterface $input
     * @return int
     */
    private function getTotalProcessNum(InputInterface $input): int
    {
        return intval($input->getOption('total') ?: 1);
    }

    /**
     * @param InputInterface $input
     * @return string
     */
    private function getProcessName(InputInterface $input): string
    {
        return $this->getName() . $this->getProcessNum($input);
    }

    /**
     * Call this function in configure()
     */
    protected function updateConfigWithProcessOptions()
    {
        /** @var Command $this */
        $this->getDefinition()->addOptions([
            new InputOption('number', null, InputOption::VALUE_OPTIONAL, 'Number of process', 1),
            new InputOption('total', null, InputOption::VALUE_OPTIONAL, 'Total processes number', 1)
        ]);
    }

    public function getSlicedItemsByProcess(array $items, InputInterface $input, OutputInterface $output): array
    {
        $processNum = $this->getProcessNum($input);
        $totalProcessesCount = $this->getTotalProcessNum($input);
        $resultItems = array_filter($items, function (int $item) use ($processNum, $totalProcessesCount) {
            return ($totalProcessesCount - ($item % $totalProcessesCount)) == $processNum;
        });
        $output->writeln('Calc items [' . $processNum . '/' . $totalProcessesCount . ']: ' .
            ($resultItems ? implode(', ', $resultItems) : '-'));

        return $resultItems;
    }
}