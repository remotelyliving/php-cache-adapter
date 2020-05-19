<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\Tests\Integration\CacheItemPool;

use Psr\Cache;
use RemotelyLiving\PHPCacheAdapter\Tests;

class AdapterValidationTest extends Tests\Integration\AbstractTestCase
{
    /**
     * @dataProvider cacheItemPoolProvider
     */
    public function testDoesNotAllowInvalidKeyForGetItem(Cache\CacheItemPoolInterface $adapter): void
    {
        foreach ($this->invalidCacheKeyProvider() as $invalidKey) {
            try {
                $adapter->getItem($invalidKey);
            } catch (Cache\InvalidArgumentException $e) {
                $this->assertInstanceOf(Cache\InvalidArgumentException::class, $e);
                continue;
            }

            $this->fail('Invalid key' . var_export($invalidKey, true) . 'should have thrown exception');
        }
    }

    /**
     * @dataProvider cacheItemPoolProvider
     */
    public function testDoesNotAllowInvalidKeyForSaveItem(Cache\CacheItemPoolInterface $adapter): void
    {
        foreach ($this->invalidCacheKeyProvider() as $invalidKey) {
            try {
                $item = $adapter->getItem($invalidKey);
                $adapter->save($item);
            } catch (Cache\InvalidArgumentException $e) {
                $this->assertInstanceOf(Cache\InvalidArgumentException::class, $e);
                continue;
            }

            $this->fail('Invalid key' . var_export($invalidKey, true) . 'should have thrown exception');
        }
    }

    /**
     * @dataProvider cacheItemPoolProvider
     */
    public function testDoesNotAllowInvalidKeyForDeleteItem(Cache\CacheItemPoolInterface $adapter): void
    {
        foreach ($this->invalidCacheKeyProvider() as $invalidKey) {
            try {
                $adapter->deleteItem($invalidKey);
            } catch (Cache\InvalidArgumentException $e) {
                $this->assertInstanceOf(Cache\InvalidArgumentException::class, $e);
                continue;
            }

            $this->fail('Invalid key' . var_export($invalidKey, true) . 'should have thrown exception');
        }
    }

    /**
     * @dataProvider cacheItemPoolProvider
     */
    public function testDoesNotAllowInvalidKeysForGetItems(Cache\CacheItemPoolInterface $adapter): void
    {
        $this->expectException(Cache\InvalidArgumentException::class);
        $adapter->getItems(['valid1', 'valid2', 'invalid heyooo'])->current();
    }

    /**
     * @dataProvider cacheItemPoolProvider
     */
    public function testDoesNotAllowInvalidKeysForDeleteItems(Cache\CacheItemPoolInterface $adapter): void
    {
        $this->expectException(Cache\InvalidArgumentException::class);
        $adapter->deleteItems(['valid1', 'valid2', 'invalid heyooo']);
    }

    /**
     * @dataProvider cacheItemPoolProvider
     */
    public function testDoesNotAllowInvalidKeysForHasItem(Cache\CacheItemPoolInterface $adapter): void
    {
        foreach ($this->invalidCacheKeyProvider() as $invalidKey) {
            try {
                $adapter->hasItem($invalidKey);
            } catch (Cache\InvalidArgumentException $e) {
                $this->assertInstanceOf(Cache\InvalidArgumentException::class, $e);
                continue;
            }

            $this->fail('Invalid key' . var_export($invalidKey, true) . 'should have thrown exception');
        }
    }
}
