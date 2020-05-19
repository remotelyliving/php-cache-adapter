<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\Tests\Unit\CacheItemPool;

use Psr\Cache;
use Psr\SimpleCache as PSRSimpleCache;
use RemotelyLiving\PHPCacheAdapter\CacheItemPool\CacheItemPool;
use RemotelyLiving\PHPCacheAdapter\Tests;

class CacheItemPoolTest extends Tests\Unit\AbstractTestCase
{
    private PSRSimpleCache\CacheInterface $simpleCache;

    private Cache\CacheItemPoolInterface $cacheItemPool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->simpleCache = $this->createMock(PSRSimpleCache\CacheInterface::class);
        $this->cacheItemPool = CacheItemPool::createFromSimpleCache($this->simpleCache);
    }

    public function testUsesNamespaceIfProvided(): void
    {
        $this->cacheItemPool = CacheItemPool::createFromSimpleCache($this->simpleCache, 'namespace');
        $this->simpleCache->expects($this->once())
            ->method('getMultiple')
            ->with(['namespace:foo'])
            ->willReturn(['namespace:foo' => null]);

        $item = $this->cacheItemPool->getItem('foo');
        $this->assertSame('foo', $item->getKey());
    }

    public function testDisregardsNamespaceIfNotProvided(): void
    {
        $this->simpleCache->expects($this->once())
            ->method('getMultiple')
            ->with(['foo'])
            ->willReturn(['foo' => null]);

        $item = $this->cacheItemPool->getItem('foo');
        $this->assertSame('foo', $item->getKey());
    }
}
