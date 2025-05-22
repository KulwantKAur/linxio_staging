<?php

namespace App\Command\Traits;

trait MemorableTrait
{
    /**
     * @param string $size
     */
    public function setMemoryLimit(string $size = '1024M')
    {
        //TODO remove completely after review
//        ini_set('memory_limit', $size);
    }
}