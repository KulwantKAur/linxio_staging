<?php

namespace App\Command\Traits;

use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\RedisStore;
use Predis\Client;

trait RedisLockTrait
{
    use LockableTrait;

    public function getLock($name)
    {
        $host = $this->params->get('redis.host');
        $port = $this->params->get('redis.port');
        $store = new RedisStore(new Client("redis://" . $host . ":" . $port . ""));
        $factory = new LockFactory($store);

        return $factory->createLock($name);
    }
}