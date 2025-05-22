<?php

namespace App\Command\Traits;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

trait DevicebleTrait
{
    /**
     * Call this function in configure()
     */
    protected function updateConfigWithDeviceOptions()
    {
        /** @var Command $this */
        $this->getDefinition()->addOptions([
            new InputOption('deviceIds', null, InputOption::VALUE_OPTIONAL, 'Device IDs via comma: "1,2,n"', ''),
            new InputOption('deviceIgnoreIds', null, InputOption::VALUE_OPTIONAL, 'Device IDs to ignore via comma: "1,2,n"', '')
        ]);
    }

    /**
     * @param InputInterface $input
     * @return array
     */
    private function getDeviceIdsByInput(InputInterface $input): array
    {
        return $input->getOption('deviceIds') ? explode(',', $input->getOption('deviceIds')) : [];
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $deviceIds
     * @return array
     */
    private function getDeviceIdsWithoutIgnored(InputInterface $input, OutputInterface $output, array $deviceIds): array
    {
        $deviceIgnoreIds = $input->getOption('deviceIgnoreIds')
            ? explode(',', $input->getOption('deviceIgnoreIds'))
            : [];

        if ($deviceIgnoreIds) {
            $output->writeln('Ignore items (' . count($deviceIgnoreIds) . '): ' . (implode(', ', $deviceIgnoreIds)));
            $deviceIds = array_diff($deviceIds, $deviceIgnoreIds);
        }

        return $deviceIds;
    }
}