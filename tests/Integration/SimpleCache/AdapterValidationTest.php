<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\Tests\Integration\SimpleCache;

use Psr\SimpleCache;
use RemotelyLiving\PHPCacheAdapter\Tests;

class AdapterValidationTest extends Tests\Integration\AbstractTestCase
{
    /**
     * @dataProvider simpleCacheProvider
     */
    public function testDoesNotAllowInvalidKeyForGet(SimpleCache\CacheInterface $adapter): void
    {
        foreach ($this->invalidCacheKeyProvider() as $invalidKey) {
            try {
                $adapter->get($invalidKey);
            } catch (SimpleCache\InvalidArgumentException $e) {
                $this->assertInstanceOf(SimpleCache\InvalidArgumentException::class, $e);
                continue;
            }

            $this->fail('Invalid key' . var_export($invalidKey, true) . 'should have thrown exception');
        }
    }

    /**
     * @dataProvider simpleCacheProvider
     */
    public function testDoesNotAllowInvalidKeyForSet(SimpleCache\CacheInterface $adapter): void
    {
        foreach ($this->invalidCacheKeyProvider() as $invalidKey) {
            try {
                $adapter->set($invalidKey, 'bar');
            } catch (SimpleCache\InvalidArgumentException $e) {
                $this->assertInstanceOf(SimpleCache\InvalidArgumentException::class, $e);
                continue;
            }

            $this->fail('Invalid key' . var_export($invalidKey, true) . 'should have thrown exception');
        }
    }

    /**
     * @dataProvider simpleCacheProvider
     */
    public function testDoesNotAllowInvalidKeyForDelete(SimpleCache\CacheInterface $adapter): void
    {
        foreach ($this->invalidCacheKeyProvider() as $invalidKey) {
            try {
                $adapter->delete($invalidKey);
            } catch (SimpleCache\InvalidArgumentException $e) {
                $this->assertInstanceOf(SimpleCache\InvalidArgumentException::class, $e);
                continue;
            }

            $this->fail('Invalid key' . var_export($invalidKey, true) . 'should have thrown exception');
        }
    }

    /**
     * @dataProvider simpleCacheProvider
     */
    public function testDoesNotAllowInvalidKeysForGetMultiple(SimpleCache\CacheInterface $adapter): void
    {
        $this->expectException(SimpleCache\InvalidArgumentException::class);
        $adapter->getMultiple(['valid1', 'valid2', 'invalid heyooo']);
    }

    /**
     * @dataProvider simpleCacheProvider
     */
    public function testDoesNotAllowInvalidKeysForSetMultiple(SimpleCache\CacheInterface $adapter): void
    {
        $this->expectException(SimpleCache\InvalidArgumentException::class);
        $adapter->setMultiple(['valid1' => 'foo', 'valid2' => 'bar', 'invalid heyooo' => 'baz']);
    }

    /**
     * @dataProvider simpleCacheProvider
     */
    public function testDoesNotAllowInvalidKeysForDeleteMultiple(SimpleCache\CacheInterface $adapter): void
    {
        $this->expectException(SimpleCache\InvalidArgumentException::class);
        $adapter->deleteMultiple(['valid1', 'valid2', 'invalid heyooo']);
    }

    /**
     * @dataProvider simpleCacheProvider
     */
    public function testDoesNotAllowInvalidKeysForHas(SimpleCache\CacheInterface $adapter): void
    {
        foreach ($this->invalidCacheKeyProvider() as $invalidKey) {
            try {
                $adapter->has($invalidKey);
            } catch (SimpleCache\InvalidArgumentException $e) {
                $this->assertInstanceOf(SimpleCache\InvalidArgumentException::class, $e);
                continue;
            }

            $this->fail('Invalid key' . var_export($invalidKey, true) . 'should have thrown exception');
        }
    }


    /**
     * @dataProvider simpleCacheProvider
     */
    public function testDoesNotAllowNonIterablesForGetMultiple(SimpleCache\CacheInterface $adapter): void
    {
        $this->expectException(SimpleCache\InvalidArgumentException::class);
        $adapter->getMultiple('not iterable');
    }

    /**
     * @dataProvider simpleCacheProvider
     */
    public function testDoesNotAllowNonIterablesForSetMultiple(SimpleCache\CacheInterface $adapter): void
    {
        $this->expectException(SimpleCache\InvalidArgumentException::class);
        $adapter->setMultiple('not iterable');
    }

    /**
     * @dataProvider simpleCacheProvider
     */
    public function testDoesNotAllowNonIterablesForDeleteMultiple(SimpleCache\CacheInterface $adapter): void
    {
        $this->expectException(SimpleCache\InvalidArgumentException::class);
        $adapter->deleteMultiple('not iterable');
    }

    /**
     * @dataProvider simpleCacheProvider
     */
    public function testDoesNotAllowForInvalidTTLForSetMultiple(SimpleCache\CacheInterface $adapter): void
    {
        $this->expectException(SimpleCache\InvalidArgumentException::class);
        $adapter->setMultiple(['foo' => 'bar'], 'baz');
    }

    /**
     * @dataProvider simpleCacheProvider
     */
    public function testDoesNotAllowForInvalidTTLForSet(SimpleCache\CacheInterface $adapter): void
    {
        $this->expectException(SimpleCache\InvalidArgumentException::class);
        $adapter->set('foo', 'bar', 'baz');
    }
}
