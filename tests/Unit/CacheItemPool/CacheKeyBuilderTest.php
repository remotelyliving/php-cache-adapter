<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\Tests\Unit\CacheItemPool;

use Psr\Cache;
use RemotelyLiving\PHPCacheAdapter\CacheItemPool\CacheKeyBuilder;
use RemotelyLiving\PHPCacheAdapter\Tests;

class CacheKeyBuilderTest extends Tests\Unit\AbstractTestCase
{
    public function testBuildsKeyWithNamespace(): void
    {
        $actual = CacheKeyBuilder::create('foo')->buildKey('bar');

        $this->assertSame('foo:bar', $actual);
    }

    public function testBuildsKeyWithoutNamespace(): void
    {
        $actual = CacheKeyBuilder::create()->buildKey('bar');

        $this->assertSame('bar', $actual);
    }

    public function testBuildsKeysWithNamespace(): void
    {
        $actual = CacheKeyBuilder::create('foo')->buildKeys(['bar', 'baz']);

        $this->assertSame(['foo:bar', 'foo:baz'], $actual);
    }

    public function testBuildsKeysWithoutNamespace(): void
    {
        $actual = CacheKeyBuilder::create()->buildKeys(['bar', 'baz']);

        $this->assertSame(['bar', 'baz'], $actual);
    }

    public function testDoesNotAllowInvalidKey(): void
    {
        $this->expectException(Cache\InvalidArgumentException::class);
        CacheKeyBuilder::create()->buildKey('hey there');
    }

    public function testDoesNotAllowInvalidKeys(): void
    {
        $this->expectException(Cache\InvalidArgumentException::class);
        CacheKeyBuilder::create()->buildKeys(['foo', 'hey there']);
    }
}
