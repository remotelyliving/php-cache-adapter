<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\Tests\Unit\SimpleCache;

use Psr\SimpleCache as PSRSimpleCache;
use RemotelyLiving\PHPCacheAdapter\SimpleCache;
use RemotelyLiving\PHPCacheAdapter\Tests;

class MemoryTest extends Tests\Unit\AbstractTestCase
{
    private PSRSimpleCache\CacheInterface $memory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->memory = SimpleCache\Memory::create();
    }

    public function testEvictsItemsAfterThresholdReached(): void
    {
        $this->memory = SimpleCache\Memory::create(2);
        $this->memory->set('key1', 'foo');
        $this->memory->set('key2', 'foo');

        $this->assertTrue($this->memory->has('key1'));
        $this->assertTrue($this->memory->has('key2'));

        $this->memory->set('key3', 'foo');

        $this->assertFalse($this->memory->has('key1'));
        $this->assertTrue($this->memory->has('key2'));
        $this->assertTrue($this->memory->has('key3'));
    }

    public function testStoresObjectsByReference(): void
    {
        $obj = new \stdClass();
        $obj->foo = 'bar';

        $this->memory->set('key1', $obj);
        $obj->foo = 'baz';

        $this->assertSame('baz', $this->memory->get('key1')->foo);
    }
}
