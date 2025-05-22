<?php


namespace App\Service\Redis;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\TraceableAdapter;

class RedisService
{
    /**
     * @var TraceableAdapter
     */
    protected $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param $key
     * @return |null
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function get($key)
    {
        $cachedItem = $this->cache->getItem($key);

        if ($cachedItem->isHit()) {
            return $this->cache->getItem($key)->get();
        } else {
            return null;
        }
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function set($key, $value)
    {
        $cachedItem = $this->cache->getItem($key);

        $cachedItem->set($value);
        $this->cache->save($cachedItem);

        return $this->cache->getItem($key)->get();
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function setToJson($key, $value)
    {
        return $this->set($key, json_encode($value));
    }

    public function getFromJson($key)
    {
        return json_decode($this->get($key), true);
    }

    /**
     * @param $key
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function deleteItem($key)
    {
        return $this->cache->deleteItem($key);
    }

    /**
     * @param $key
     * @param $value
     * @param int|null $ttl
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function setToJsonTtl($key, $value, int $ttl = null)
    {
        return $this->setTtl($key, json_encode($value), $ttl);
    }

    /**
     * @param $key
     * @param $value
     * @param int|null $ttl
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function setTtl($key, $value, int $ttl = null)
    {
        $cachedItem = $this->cache->getItem($key);

        $cachedItem->set($value);
        $cachedItem->expiresAfter($ttl);
        $this->cache->save($cachedItem);

        return $this->cache->getItem($key)->get();
    }
}