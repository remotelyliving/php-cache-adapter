<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\Tests\Integration\SimpleCache;

use Psr\SimpleCache as PSRSimpleCache;
use RemotelyLiving\PHPCacheAdapter\SimpleCache;
use RemotelyLiving\PHPCacheAdapter\Tests;

class AdapterCachingTest extends Tests\Integration\AbstractTestCase
{
    public function testFactoryMethods(): void
    {
        $this->assertInstanceOf(
            PSRSimpleCache\CacheInterface::class,
            SimpleCache\Memory::create()
        );

        $this->assertInstanceOf(
            PSRSimpleCache\CacheInterface::class,
            SimpleCache\Redis::create($this->redis)
        );

        $this->assertInstanceOf(
            PSRSimpleCache\CacheInterface::class,
            SimpleCache\Memcached::create($this->memcached)
        );

        $this->assertInstanceOf(
            PSRSimpleCache\CacheInterface::class,
            SimpleCache\APCu::create()
        );
    }

    /**
     * @dataProvider simpleCacheProvider
     */
    public function testSetsDefaultTTLSeconds(SimpleCache\AbstractAdapter $adapter): void
    {
        $this->assertNull($adapter->getDefaultTTLSeconds());

        $adapter->setDefaultTTLSeconds(200);

        $this->assertSame(200, $adapter->getDefaultTTLSeconds());
    }

    /**
     * @dataProvider simpleCacheProvider
     */
    public function testSetsAndGetsSingleValuesFromCache(SimpleCache\AbstractAdapter $adapter): void
    {
        $adapter->clear();
        $adapter->setDefaultTTLSeconds(2);
        $this->assertFalse($adapter->has('foo'));
        $this->assertNull($adapter->get('foo'));
        $this->assertSame('defaultValue', $adapter->get('foo', 'defaultValue'));

        $this->assertTrue($adapter->set('foo', 'bar'));
        $this->assertSame('bar', $adapter->get('foo', 'defaultValue'));
        $this->assertTrue($adapter->has('foo'));
        sleep(3);

        $this->assertFalse($adapter->has('foo'));
        $this->assertNull($adapter->get('foo'));
    }

    /**
     * @dataProvider simpleCacheProvider
     */
    public function testSetsAndGetsMultipleValuesFromCache(SimpleCache\AbstractAdapter $adapter): void
    {
        $adapter->clear();

        $this->assertFalse($adapter->has('foo'));
        $this->assertFalse($adapter->has('bar'));
        $this->assertFalse($adapter->has('baz'));
        $this->assertEquals(
            ['foo' => null, 'bar' => null, 'baz' => null],
            iterator_to_array($adapter->getMultiple(['foo', 'bar', 'baz']))
        );

        $this->assertEquals(
            ['foo' => 'defaultValue', 'bar' => 'defaultValue', 'baz' => 'defaultValue'],
            iterator_to_array($adapter->getMultiple(['foo', 'bar', 'baz'], 'defaultValue'))
        );

        $this->assertTrue($adapter->setMultiple(['foo' => 'hey', 'bar' => 'there'], 2));
        $this->assertEquals(
            ['foo' => 'hey', 'bar' => 'there', 'baz' => 'defaultValue'],
            iterator_to_array($adapter->getMultiple(['foo', 'bar', 'baz'], 'defaultValue'))
        );

        $this->assertTrue($adapter->has('foo'));
        $this->assertTrue($adapter->has('bar'));
        $this->assertFalse($adapter->has('baz'));

        sleep(3);

        $this->assertFalse($adapter->has('foo'));
        $this->assertNull($adapter->get('foo'));
        $this->assertFalse($adapter->has('bar'));
        $this->assertNull($adapter->get('bar'));
        $this->assertFalse($adapter->has('baz'));
        $this->assertNull($adapter->get('baz'));
    }

    /**
     * @dataProvider simpleCacheProvider
     */
    public function testDeletesSingleValuesFromCache(SimpleCache\AbstractAdapter $adapter): void
    {
        $adapter->clear();

        $this->assertFalse($adapter->has('foo'));
        $this->assertNull($adapter->get('foo'));
        $this->assertTrue($adapter->set('foo', 'bar'));

        $this->assertSame('bar', $adapter->get('foo'));
        $this->assertTrue($adapter->delete('foo'));

        $this->assertFalse($adapter->has('foo'));
        $this->assertNull($adapter->get('foo'));
    }

    /**
     * @dataProvider simpleCacheProvider
     */
    public function testDeletesMultipleValuesFromCache(SimpleCache\AbstractAdapter $adapter): void
    {
        $adapter->clear();

        $this->assertFalse($adapter->has('foo'));
        $this->assertNull($adapter->get('foo'));
        $this->assertFalse($adapter->has('bar'));
        $this->assertNull($adapter->get('bar'));
        $this->assertFalse($adapter->has('baz'));
        $this->assertNull($adapter->get('baz'));
        $this->assertTrue($adapter->setMultiple(['foo' => 'hey', 'bar' => 'you', 'baz' => 'all']));
        $this->assertTrue($adapter->deleteMultiple(['foo', 'baz']));

        $this->assertFalse($adapter->has('foo'));
        $this->assertFalse($adapter->has('baz'));
        $this->assertSame('you', $adapter->get('bar'));
    }

    /**
     * @dataProvider simpleCacheProvider
     */
    public function testClearsAllFromCache(SimpleCache\AbstractAdapter $adapter): void
    {
        $adapter->clear();

        $this->assertTrue($adapter->setMultiple(['foo' => 'hey', 'bar' => 'you', 'baz' => 'all']));
        $this->assertTrue($adapter->has('foo'));
        $this->assertTrue($adapter->has('bar'));
        $this->assertTrue($adapter->has('baz'));

        $this->assertTrue($adapter->clear());

        $this->assertFalse($adapter->has('foo'));
        $this->assertFalse($adapter->has('bar'));
        $this->assertFalse($adapter->has('baz'));
    }

    /**
     * @dataProvider simpleCacheProvider
     */
    public function testTakesDateTimeAsTTLToo(SimpleCache\AbstractAdapter $adapter): void
    {
        $dt = (new \DateTime('now'))
            ->add(\DateInterval::createFromDateString('2 seconds'));

        $adapter->set('foo', 'bar', $dt);
        $this->assertSame('bar', $adapter->get('foo'));
        sleep(3);
        $this->assertSame('default', $adapter->get('foo', 'default'));
    }
}
