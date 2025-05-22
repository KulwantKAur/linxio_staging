<?php

namespace App\Service\Tracker\Command\Topflytech\Model\TLD2;

use App\Service\Tracker\Command\Topflytech\Model\OdometerBaseCommand;

class OdometerCommand extends OdometerBaseCommand
{
    /**
     * @example MILEAGEC,0000,A#
     * @inheritDoc
     */
    public function getSetCommand(): ?string
    {
        $value = $this->getValue() ?: 0;

        return 'MILEAGEC,' . $value . '#';
    }
}