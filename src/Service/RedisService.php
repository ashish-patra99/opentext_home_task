<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Redi service class
 */
class RedisService
{
    private CacheInterface $cache;

    /**
     * default constructor
     *
     * @param CacheInterface $cache
     * 
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * function to set key value with redis
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * 
     * @return void
     * 
     */
    public function setKeyValue(string $key, $value, int $ttl = 3600): void
    {
        $this->cache->get($key, function (ItemInterface $item) use ($value, $ttl) {
            $item->expiresAfter($ttl);
            $item->set($value);
        });
    }

    /**
     * function to get key value with redis
     *
     * @param string $key
     * 
     * @return [type]
     * 
     */
    public function getKeyValue(string $key)
    {
       
        return $this->cache->get($key, function (ItemInterface $item) {
           return $item->get();
        });
    }
}
