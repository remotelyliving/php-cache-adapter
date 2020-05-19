<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\Tests\Unit\CacheItemPool;

use RemotelyLiving\PHPCacheAdapter\CacheItemPool;
use RemotelyLiving\PHPCacheAdapter\SimpleCache;
use RemotelyLiving\PHPCacheAdapter\Tests;

class ChainBuilderTest extends Tests\Unit\AbstractTestCase
{
    private \Memcached $memcached;

    private \Redis $redis;

    protected function setUp(): void
    {
        $this->memcached = $this->createMock(\Memcached::class);
        $this->redis = $this->createMock(\Redis::class);
    }

    public function testBuildsChainAdapter(): void
    {
        $chain = SimpleCache\Chain::create(
            SimpleCache\Memory::create(),
            SimpleCache\APCu::create(),
            SimpleCache\Redis::create($this->redis),
            SimpleCache\Memcached::create($this->memcached)
        );
        $expected = CacheItemPool\CacheItemPool::createFromSimpleCache($chain, 'foo', 300);
        $actual = CacheItemPool\ChainBuilder::create('foo', 300)
            ->addMemory()
            ->addAPCu()
            ->addRedis($this->redis)
            ->addMemcached($this->memcached)
            ->build();

        $this->assertEquals($expected, $actual);
    }
}
