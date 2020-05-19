<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\CacheItemPool;

use Psr\Cache;
use Psr\SimpleCache as PSRSimpleCache;
use RemotelyLiving\PHPCacheAdapter\SimpleCache;

final class CacheItemPool implements Cache\CacheItemPoolInterface
{
    private PSRSimpleCache\CacheInterface $cache;

    /**
     * @var array<string|int, array<string, \RemotelyLiving\PHPCacheAdapter\CacheItemPool\CacheItem>>
     */
    private array $deferred = [];

    private CacheKeyBuilder $cacheKeyBuilder;

    private ?int $defaultTTL = null;

    public function __destruct()
    {
        $this->commit();
    }

    private function __construct(
        PSRSimpleCache\CacheInterface $cache,
        ?string $namespace = null,
        int $defaultTTL = null
    ) {
        $this->cache = $cache;
        $this->cacheKeyBuilder = CacheKeyBuilder::create($namespace);
        $this->defaultTTL = $defaultTTL;
    }

    public static function createRedis(
        \Redis $redis,
        ?string $namespace = null,
        int $defaultTTL = null
    ): Cache\CacheItemPoolInterface {
        return new self(SimpleCache\Redis::create($redis), $namespace, $defaultTTL);
    }

    public static function createMemcached(
        \Memcached $memcached,
        ?string $namespace = null,
        int $defaultTTL = null
    ): Cache\CacheItemPoolInterface {
        return new self(SimpleCache\Memcached::create($memcached), $namespace, $defaultTTL);
    }

    public static function createMemory(
        int $maxItems = null,
        ?string $namespace = null,
        int $defaultTTL = null
    ): Cache\CacheItemPoolInterface {
        return new self(SimpleCache\Memory::create($maxItems), $namespace, $defaultTTL);
    }

    public static function createAPCu(
        ?string $namespace = null,
        int $defaultTTL = null
    ): Cache\CacheItemPoolInterface {
        return new self(SimpleCache\APCu::create(), $namespace, $defaultTTL);
    }

    public static function createFromSimpleCache(
        PSRSimpleCache\CacheInterface $cache,
        ?string $namespace = null,
        int $defaultTTL = null
    ): Cache\CacheItemPoolInterface {
        return new self($cache, $namespace, $defaultTTL);
    }

    /**
     * @inheritDoc
     */
    public function getItem($key)
    {
        return $this->getItems([$key])->current();
    }

    /**
     * @inheritDoc
     */
    public function getItems(array $keys = []): \Generator
    {
        foreach ($this->cache->getMultiple($this->cacheKeyBuilder->buildKeys($keys)) as $key => $item) {
            $normalizedKey = $this->cacheKeyBuilder->removeNamespace($key);
            yield $normalizedKey => ($item) ? $item->setAsHit() : CacheItem::create($normalizedKey);
        }
    }

    /**
     * @inheritDoc
     */
    public function hasItem($key): bool
    {
        return $this->cache->has($this->cacheKeyBuilder->buildKey($key));
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        $this->deferred = [];
        return $this->cache->clear();
    }

    /**
     * @inheritDoc
     */
    public function deleteItem($key): bool
    {
        return $this->deleteItems([$key]);
    }

    /**
     * @inheritDoc
     */
    public function deleteItems(array $keys): bool
    {
        return $this->cache->deleteMultiple($this->cacheKeyBuilder->buildKeys($keys));
    }

    /**
     * @inheritDoc
     */
    public function save(Cache\CacheItemInterface $item): bool
    {
        $this->saveDeferred($item);

        return $this->commit();
    }

    /**
     * @inheritDoc
     */
    public function saveDeferred(Cache\CacheItemInterface $item): bool
    {
        $this->deferred[$item->getTTL()][$this->cacheKeyBuilder->buildKey($item->getKey())] = $item;
        return true;
    }

    /**
     * @inheritDoc
     */
    public function commit(): bool
    {
        foreach ($this->deferred as $ttl => $items) {
            $this->cache->setMultiple($items, ($ttl !== '') ? $ttl : $this->defaultTTL);
            unset($this->deferred[$ttl]);
        }

        return true;
    }
}
