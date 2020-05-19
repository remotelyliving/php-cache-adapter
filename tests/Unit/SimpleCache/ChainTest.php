<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\Tests\Unit\SimpleCache;

use Psr\SimpleCache as PSRSimpleCache;
use RemotelyLiving\PHPCacheAdapter\CacheItemPool;
use RemotelyLiving\PHPCacheAdapter\SimpleCache;
use RemotelyLiving\PHPCacheAdapter\Tests;

class ChainTest extends Tests\Unit\AbstractTestCase
{
    private PSRSimpleCache\CacheInterface $simpleCache1;

    private PSRSimpleCache\CacheInterface $simpleCache2;

    private PSRSimpleCache\CacheInterface $simpleCache3;

    private PSRSimpleCache\CacheInterface $chain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->simpleCache1 = $this->createMock(PSRSimpleCache\CacheInterface::class);
        $this->simpleCache2 = $this->createMock(PSRSimpleCache\CacheInterface::class);
        $this->simpleCache3 = $this->createMock(PSRSimpleCache\CacheInterface::class);

        $this->chain = SimpleCache\Chain::create($this->simpleCache1, $this->simpleCache2, $this->simpleCache3);
    }

    public function testCallsThroughCacheAdaptersFIFOToGet(): void
    {
        $this->simpleCache1->method('getMultiple')
            ->with(['foo'], 'default')
            ->willReturn($this->arrayToGenerator(['foo' => 'default']));

        $this->simpleCache2->method('getMultiple')
            ->with(['foo'], 'default')
            ->willReturn($this->arrayToGenerator(['foo' => 'bar']));

        $this->simpleCache3->expects($this->never())
            ->method('getMultiple');

        $this->assertSame('bar', $this->chain->get('foo', 'default'));
    }

    public function testCallsThroughCacheAdaptersFIFOToGetMultiple(): void
    {
        $this->simpleCache1->method('getMultiple')
            ->with(['foo'], 'default')
            ->willReturn($this->arrayToGenerator(['foo' => 'default']));

        $this->simpleCache2->method('getMultiple')
            ->with(['foo'], 'default')
            ->willReturn($this->arrayToGenerator(['foo' => 'bar']));

        $this->simpleCache3->expects($this->never())
            ->method('getMultiple');

        $this->assertSame(['foo' => 'bar'], $this->generatorToArray($this->chain->getMultiple(['foo'], 'default')));
    }

    public function testCallsThroughCacheAdaptersFIFOToGetMultipleWithObject(): void
    {
        $defaultItem = CacheItemPool\CacheItem::create('foo');
        $returnedItem = CacheItemPool\CacheItem::create('foo')
            ->set('bar');

        $this->simpleCache1->method('getMultiple')
            ->with(['foo'], $defaultItem)
            ->willReturn($this->arrayToGenerator(['foo' => $defaultItem]));

        $this->simpleCache2->method('getMultiple')
            ->with(['foo'], $defaultItem)
            ->willReturn($this->arrayToGenerator(['foo' => $returnedItem]));

        $this->simpleCache3->expects($this->never())
            ->method('getMultiple');

        $this->assertSame(
            ['foo' => $returnedItem],
            $this->generatorToArray($this->chain->getMultiple(['foo'], $defaultItem))
        );
    }

    public function testCallsThroughCacheAdaptersFIFOToGetHas(): void
    {
        $this->simpleCache1->method('has')
            ->with('foo')
            ->willReturn(false);

        $this->simpleCache2->method('has')
            ->with('foo')
            ->willReturn(true);

        $this->simpleCache3->expects($this->never())
            ->method('has');

        $this->assertTrue($this->chain->has('foo'));
    }

    public function testCallsThroughCacheAdaptersEvenIfNoValueFoundAndReturnsDefault(): void
    {
        $this->simpleCache1->method('getMultiple')
            ->with(['foo'], 'default')
            ->willReturn($this->arrayToGenerator(['foo' => 'default']));

        $this->simpleCache2->method('getMultiple')
            ->with(['foo'], 'default')
            ->willReturn($this->arrayToGenerator(['foo' => 'default']));

        $this->simpleCache3->method('getMultiple')
            ->with(['foo'], 'default')
            ->willReturn($this->arrayToGenerator(['foo' => 'default']));

        $this->assertSame('default', $this->chain->get('foo', 'default'));
    }

    public function testCallsThroughCacheAdaptersToSet(): void
    {
        $this->simpleCache1->expects($this->once())
            ->method('setMultiple')
            ->with(['foo' => 'bar'], 300)
            ->willReturn(true);

        $this->simpleCache2->expects($this->once())
            ->method('setMultiple')
            ->with(['foo' => 'bar'], 300)
            ->willReturn(true);

        $this->simpleCache3->expects($this->once())
            ->method('setMultiple')
            ->with(['foo' => 'bar'], 300)
            ->willReturn(true);

        $this->assertTrue($this->chain->set('foo', 'bar', 300));
    }

    public function testCallsThroughCacheAdaptersAndReturnsFalseIfOneFailed(): void
    {
        $this->simpleCache1->expects($this->once())
            ->method('setMultiple')
            ->with(['foo' => 'bar'], 300)
            ->willReturn(true);

        $this->simpleCache2->expects($this->once())
            ->method('setMultiple')
            ->with(['foo' => 'bar'], 300)
            ->willReturn(false);

        $this->simpleCache3->expects($this->once())
            ->method('setMultiple')
            ->with(['foo' => 'bar'], 300)
            ->willReturn(true);

        $this->assertFalse($this->chain->set('foo', 'bar', 300));
    }

    public function testCallsThroughCacheAdaptersToSetMultiple(): void
    {
        $this->simpleCache1->expects($this->once())
            ->method('setMultiple')
            ->with(['foo' => 'bar'], 100)
            ->willReturn(true);

        $this->simpleCache2->expects($this->once())
            ->method('setMultiple')
            ->with(['foo' => 'bar'], 100)
            ->willReturn(true);

        $this->simpleCache3->expects($this->once())
            ->method('setMultiple')
            ->with(['foo' => 'bar'], 100)
            ->willReturn(true);

        $this->assertTrue($this->chain->setMultiple(['foo' => 'bar'], 100));
    }


    public function testCallsThroughCacheAdaptersToDelete(): void
    {
        $this->simpleCache1->expects($this->once())
            ->method('deleteMultiple')
            ->with(['foo'])
            ->willReturn(true);

        $this->simpleCache2->expects($this->once())
            ->method('deleteMultiple')
            ->with(['foo'])
            ->willReturn(true);

        $this->simpleCache3->expects($this->once())
            ->method('deleteMultiple')
            ->with(['foo'])
            ->willReturn(true);

        $this->assertTrue($this->chain->delete('foo'));
    }

    public function testCallsThroughCacheAdaptersToDeleteMultiple(): void
    {
        $this->simpleCache1->expects($this->once())
            ->method('deleteMultiple')
            ->with(['foo'])
            ->willReturn(true);

        $this->simpleCache2->expects($this->once())
            ->method('deleteMultiple')
            ->with(['foo'])
            ->willReturn(true);

        $this->simpleCache3->expects($this->once())
            ->method('deleteMultiple')
            ->with(['foo'])
            ->willReturn(true);

        $this->assertTrue($this->chain->deleteMultiple(['foo']));
    }

    public function testCallsThroughCacheAdaptersToClearCache(): void
    {
        $this->simpleCache1->expects($this->once())
            ->method('clear');

        $this->simpleCache2->expects($this->once())
            ->method('clear');

        $this->simpleCache3->expects($this->once())
            ->method('clear');

        $this->assertTrue($this->chain->clear());
    }
}
