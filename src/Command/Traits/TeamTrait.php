<?php

namespace App\Command\Traits;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

trait TeamTrait
{
    /**
     * Call this function in configure()
     */
    protected function updateConfigWithTeamOptions()
    {
        /** @var Command $this */
        $this->getDefinition()->addOption(
            new InputOption('teamIds', null, InputOption::VALUE_OPTIONAL, 'Team IDs via comma: "1,2,n"', [])
        );
    }

    /**
     * @param InputInterface $input
     * @return array
     */
    private function getTeamIdsByInput(InputInterface $input): array
    {
        return $input->getOption('teamIds') ? explode(',', $input->getOption('teamIds')): [];
    }
}