<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\Tests\Unit\CacheItemPool;

use Psr\Cache;
use RemotelyLiving\PHPCacheAdapter\CacheItemPool;
use RemotelyLiving\PHPCacheAdapter\Tests;

class CacheItemTest extends Tests\Unit\AbstractTestCase
{
    public function testBasicState(): void
    {
        $item = CacheItemPool\CacheItem::create('foo');
        $this->assertNull($item->get());
        $this->assertNull($item->getTTL());
        $this->assertFalse($item->isHit());
        $this->assertSame('foo', $item->getKey());
    }

    public function testModifiedState(): void
    {
        $item = CacheItemPool\CacheItem::create('foo')
            ->set(['hey there'])
            ->setAsHit()
            ->expiresAfter(123);

        $this->assertSame(['hey there'], $item->get());
        $this->assertSame(123, $item->getTTL());
        $this->assertTrue($item->isHit());
        $this->assertSame('foo', $item->getKey());
    }

    public function testExpiresAfterWithTimestamp(): void
    {
        $item = CacheItemPool\CacheItem::create('foo')
            ->expiresAfter(123);

        $this->assertSame(123, $item->getTTL());
    }

    public function testExpiresAfterWithNull(): void
    {
        $item = CacheItemPool\CacheItem::create('foo')
            ->expiresAfter(null);

        $this->assertNull($item->getTTL());
    }

    /**
     * @dataProvider expiresAfterInvalidArgumentProvider
     */
    public function testExpiresAfterWithInvalidArgument($value): void
    {
        $this->expectException(Cache\InvalidArgumentException::class);
        CacheItemPool\CacheItem::create('foo')
            ->expiresAfter($value);
    }

    public function testExpiresAfterWithDateTime(): void
    {
        $dt = (new \DateTime('now'))
            ->add(\DateInterval::createFromDateString('1 minute'));

        $item = CacheItemPool\CacheItem::create('foo')
            ->expiresAfter($dt);

        $this->assertGreaterThan(0, $item->getTTL());
        $this->assertLessThanOrEqual(60, $item->getTTL());
    }

    public function testExpiresAtWithDateTime(): void
    {
        $dt = (new \DateTime('now'))
            ->add(\DateInterval::createFromDateString('1 minute'));

        $item = CacheItemPool\CacheItem::create('foo')
            ->expiresAt($dt);

        $this->assertGreaterThan(0, $item->getTTL());
        $this->assertLessThanOrEqual(60, $item->getTTL());
    }

    public function testExpiresAtWithNull(): void
    {
        $item = CacheItemPool\CacheItem::create('foo')
            ->expiresAt(null);

        $this->assertNull($item->getTTL());
    }

    /**
     * @dataProvider expiresAtInvalidArgumentProvider
     */
    public function testExpiresAtWithInvalidArgument($value): void
    {
        $this->expectException(Cache\InvalidArgumentException::class);
        CacheItemPool\CacheItem::create('foo')
            ->expiresAt($value);
    }

    public function testIsSerializableNullState(): void
    {
        $item = CacheItemPool\CacheItem::create('foo');

        $this->assertEquals($item, \unserialize(serialize($item)));
    }

    public function testIsSerializableSetState(): void
    {
        $item = CacheItemPool\CacheItem::create('foo')
            ->expiresAfter(123)
            ->set(['foo' => 'bar']);

        $this->assertEquals($item, \unserialize(serialize($item)));
    }
    public function testIsHitResetEveryTimeUnserialized(): void
    {
        $item = CacheItemPool\CacheItem::create('foo')
            ->expiresAfter(123)
            ->set(['foo' => 'bar']);

        $this->assertFalse(\unserialize(serialize($item))->isHit());
    }

    public function expiresAtInvalidArgumentProvider(): array
    {
        return [
            [true],
            [false],
            [[]],
            [123],
            [100.00],
            [new \stdClass()],
            ['hey']
        ];
    }

    public function expiresAfterInvalidArgumentProvider(): array
    {
        return [
            [true],
            [false],
            [[]],
            [100.00],
            [new \stdClass()],
            ['hey']
        ];
    }
}
