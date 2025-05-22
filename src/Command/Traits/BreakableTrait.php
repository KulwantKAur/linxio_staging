<?php

namespace App\Command\Traits;

trait BreakableTrait
{
    /**
     * @param $jobTtl
     */
    public function breakScriptByTTL($jobTtl)
    {
        ini_set('max_execution_time', $jobTtl);
    }
}