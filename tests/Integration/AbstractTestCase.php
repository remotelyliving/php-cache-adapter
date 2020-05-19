<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Psr\Cache;
use Psr\SimpleCache as PSRSimpleCache;
use RemotelyLiving\PHPCacheAdapter\CacheItemPool;
use RemotelyLiving\PHPCacheAdapter\SimpleCache;

abstract class AbstractTestCase extends TestCase
{
    private const NAMESPACE = 'foospace';

    protected \Memcached $memcached;

    protected \Redis $redis;

    protected function setUp(): void
    {
        if (!extension_loaded('memcached')) {
            $this->markTestSkipped('Memcached must be installed to run run integration tests');
        }

        if (!extension_loaded('redis')) {
            $this->markTestSkipped('Redis must be installed to run run integration tests');
        }

        if (!extension_loaded('apcu')) {
            $this->markTestSkipped('APCu must be installed to run run integration tests');
        }

        $this->memcached = new \Memcached();
        $this->memcached->addServer(
            getenv('MEMCACHE_HOST') ?: '127.0.0.1',
            (int) getenv('MEMCACHE_PORT') ?: 11211
        );

        $this->redis = new \Redis();
        $this->redis->pconnect(
            getenv('REDIS_HOST') ?: '127.0.0.1',
            (int) getenv('REDIS_PORT') ?: 6379,
            30
        );
    }

    protected function createSimpleMemcachedAdapter(): PSRSimpleCache\CacheInterface
    {
        return SimpleCache\Memcached::create($this->memcached);
    }

    protected function createSimpleRedisAdapter(): PSRSimpleCache\CacheInterface
    {
        return SimpleCache\Redis::create($this->redis);
    }

    protected function createSimpleMemoryAdapter(int $maxItems = null): PSRSimpleCache\CacheInterface
    {
        return SimpleCache\Memory::create($maxItems);
    }

    protected function createSimpleAPCuAdapter(): PSRSimpleCache\CacheInterface
    {
        return SimpleCache\APCu::create();
    }

    protected function createSimpleChainAdapter(): PSRSimpleCache\CacheInterface
    {
        $chain = SimpleCache\Chain::create(
            $this->createSimpleMemoryAdapter(),
            $this->createSimpleRedisAdapter(),
            $this->createSimpleMemcachedAdapter(),
        );

        $chain->flush();

        return $chain;
    }

    protected function createMemcacheCacheItemPool(): Cache\CacheItemPoolInterface
    {
        return CacheItemPool\CacheItemPool::createMemcached($this->memcached, self::NAMESPACE);
    }

    protected function createRedisCacheItemPool(): Cache\CacheItemPoolInterface
    {
        return CacheItemPool\CacheItemPool::createRedis($this->redis, self::NAMESPACE);
    }

    protected function createMemoryCacheItemPool(): Cache\CacheItemPoolInterface
    {
        return CacheItemPool\CacheItemPool::createMemory(null, self::NAMESPACE);
    }

    protected function createAPCuCacheItemPool(): Cache\CacheItemPoolInterface
    {
        return CacheItemPool\CacheItemPool::createAPCu(self::NAMESPACE);
    }

    protected function createChainCacheItemPool(): Cache\CacheItemPoolInterface
    {
        return CacheItemPool\ChainBuilder::create(self::NAMESPACE)
            ->addMemory()
            ->addAPCu()
            ->addMemcached($this->memcached)
            ->addRedis($this->redis)
            ->build();
    }

    public function cacheItemPoolProvider(): array
    {
        $this->setUp();

        return [
            'CacheItemPool Redis Adapter' => [$this->createRedisCacheItemPool()],
            'CacheItemPool Memcached Adapter' => [$this->createMemoryCacheItemPool()],
            'CacheItemPool Memory Adapter' => [$this->createMemcacheCacheItemPool()],
            'CacheItemPool APCu Adapter' => [$this->createAPCuCacheItemPool()],
            'CacheItemPool Chain' => [$this->createChainCacheItemPool()],
        ];
    }

    public function simpleCacheProvider(): array
    {
        $this->setUp();
        return [
            'SimpleCache Redis Adapter' => [$this->createSimpleRedisAdapter()],
            'SimpleCache Memcached Adapter' => [$this->createSimpleMemcachedAdapter()],
            'SimpleCache Memory Adapter' => [$this->createSimpleMemoryAdapter()],
            'SimpleCache Chain Adapter' => [$this->createSimpleChainAdapter()],
            'SimpleCache APCu Adapter' => [$this->createSimpleAPCuAdapter()],
        ];
    }

    public function invalidCacheKeyProvider(): array
    {
        return [
            'string with space' => ['hey there'],
            'object' => [new \stdClass()],
            'double' => [100.000],
            'bool true' => [true],
            'bool false' => [false],
            'array' => [[]],
        ];
    }
}
