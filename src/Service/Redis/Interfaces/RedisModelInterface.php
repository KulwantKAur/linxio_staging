<?php

namespace App\Service\Redis\Interfaces;

interface RedisModelInterface
{
    /**
     * @param int $id
     * @return mixed
     */
    public function getKeyById(int $id);
}