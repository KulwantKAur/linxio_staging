<?php

namespace App\Service\Redis;

use Symfony\Contracts\Cache\TagAwareCacheInterface;

class MemoryDbService
{
    private TagAwareCacheInterface $cache;

    /**
     * @param TagAwareCacheInterface $appCacheMemoryDb
     */
    public function __construct(TagAwareCacheInterface $appCacheMemoryDb)
    {
        $this->cache = $appCacheMemoryDb;
    }

    /**
     * @param $key
     * @return null
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
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getFromJson($key)
    {
        $data = $this->get($key);

        return $data ? json_decode($data, true) : null;
    }

    /**
     * @param $key
     * @param $value
     * @param array $tags
     * @return mixed
     */
    public function set($key, $value, $tags = [])
    {
        $cachedItem = $this->cache->getItem($key);
        $cachedItem->set($value);
        $cachedItem->tag($tags);
        $this->cache->save($cachedItem);

        return $this->cache->getItem($key)->get();
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function setToJson($key, $value, $tags = [])
    {
        return $this->set($key, json_encode($value), $tags);
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
     * @param $tags
     * @param int|null $ttl
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function setToJsonTtl($key, $value, $tags = [], int $ttl = null)
    {
        return $this->setTtl($key, json_encode($value), $tags, $ttl);
    }

    /**
     * @param $key
     * @param $value
     * @param int|null $ttl
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function setTtl($key, $value, $tags = [], int $ttl = null)
    {
        $cachedItem = $this->cache->getItem($key);

        $cachedItem->set($value);
        $cachedItem->tag($tags);
        $cachedItem->expiresAfter($ttl);
        $this->cache->save($cachedItem);

        return $this->cache->getItem($key)->get();
    }

    /**
     * Delete all data linked with tags
     * @param array $tags
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function invalidateTags(array $tags = [])
    {
        return $this->cache->invalidateTags($tags);
    }
}
