<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\Tests\Integration\CacheItemPool;

use Psr\Cache;
use RemotelyLiving\PHPCacheAdapter\CacheItemPool;
use RemotelyLiving\PHPCacheAdapter\Tests;

class AdapterCachingTest extends Tests\Integration\AbstractTestCase
{
    public function testFactoryMethods(): void
    {
        $this->assertInstanceOf(
            Cache\CacheItemPoolInterface::class,
            CacheItemPool\CacheItemPool::createMemory()
        );

        $this->assertInstanceOf(
            Cache\CacheItemPoolInterface::class,
            CacheItemPool\CacheItemPool::createRedis($this->redis)
        );

        $this->assertInstanceOf(
            Cache\CacheItemPoolInterface::class,
            CacheItemPool\CacheItemPool::createMemcached($this->memcached)
        );

        $this->assertInstanceOf(
            Cache\CacheItemPoolInterface::class,
            CacheItemPool\CacheItemPool::createFromSimpleCache($this->createSimpleChainAdapter())
        );

        $this->assertInstanceOf(
            Cache\CacheItemPoolInterface::class,
            CacheItemPool\CacheItemPool::createAPCu()
        );
    }

    /**
     * @dataProvider cacheItemPoolProvider
     */
    public function testSetsAndGetsSingleItemsFromCache(Cache\CacheItemPoolInterface $pool): void
    {
        $pool->clear();
        $item = $pool->getItem('hey');
        $this->assertFalse($item->isHit());
        $this->assertNull($item->get());

        $item->set(['foo' => 'bar']);
        $pool->save($item);

        $fetchedItem = $pool->getItem('hey');
        $this->assertEquals(['foo' => 'bar'], $fetchedItem->get());
        $this->assertTrue($fetchedItem->isHit());
    }

    /**
     * @dataProvider cacheItemPoolProvider
     */
    public function testSetsAndGetsSingleItemsFromCacheWithTTL(Cache\CacheItemPoolInterface $pool): void
    {
        $pool->clear();

        $item = $pool->getItem('hey');
        $this->assertFalse($item->isHit());
        $this->assertNull($item->get());

        $item->set(['foo' => 'bar'])
            ->expiresAfter(2);

        $pool->save($item);

        $fetchedItem = $pool->getItem('hey');
        $this->assertEquals(['foo' => 'bar'], $fetchedItem->get());
        $this->assertTrue($fetchedItem->isHit());

        sleep(3);

        $evictedItem = $pool->getItem('hey');
        $this->assertFalse($evictedItem->isHit());
        $this->assertNull($evictedItem->get());
    }

    /**
     * @dataProvider cacheItemPoolProvider
     */
    public function testSetsAndGetsMultipleItems(Cache\CacheItemPoolInterface $pool): void
    {
        $pool->clear();

        $itemKeys = [];
        foreach (range(1, 1000) as $number) {
            $itemKeys[] = "key-{$number}";
        }

        foreach ($pool->getItems($itemKeys) as $item) {
            $this->assertFalse($item->isHit());
            $item->set(random_bytes(12))
                ->expiresAfter(mt_rand(10, 300));

            $pool->saveDeferred($item);
        }

        $this->assertTrue($pool->commit());

        $savedKeys = [];
        foreach ($pool->getItems($itemKeys) as $item) {
            $this->assertTrue($item->isHit());
            $savedKeys[] = $item->getKey();
        }

        $this->assertEquals($itemKeys, $savedKeys);
    }

    /**
     * @dataProvider cacheItemPoolProvider
     */
    public function testDeletesSingleValuesFromCache(Cache\CacheItemPoolInterface $pool): void
    {
        $pool->clear();
        $item = $pool->getItem('hey');
        $item->set(['foo' => 'bar']);
        $pool->save($item);

        $fetchedItem = $pool->getItem('hey');
        $this->assertEquals(['foo' => 'bar'], $fetchedItem->get());
        $this->assertTrue($fetchedItem->isHit());

        $pool->deleteItem('hey');

        $deletedItem = $pool->getItem('hey');
        $this->assertFalse($deletedItem->isHit());
        $this->assertNull($deletedItem->get());
    }

    /**
     * @dataProvider cacheItemPoolProvider
     */
    public function testDeletesMultipleValuesFromCache(Cache\CacheItemPoolInterface $pool): void
    {
        $pool->clear();

        $itemKeys = [];
        foreach (range(1, 10) as $number) {
            $itemKeys[] = "key:{$number}";
        }

        foreach ($pool->getItems($itemKeys) as $item) {
            $this->assertFalse($item->isHit());
            $item->set(random_bytes(12));
            $pool->saveDeferred($item);
        }

        $pool->commit();

        $savedKeys = [];
        foreach ($pool->getItems($itemKeys) as $item) {
            $this->assertTrue($item->isHit());
            $savedKeys[] = $item->getKey();
        }

        $this->assertEquals($itemKeys, $savedKeys);

        $pool->deleteItems($itemKeys);

        foreach ($pool->getItems($itemKeys) as $item) {
            $this->assertFalse($item->isHit());
        }
    }
}
