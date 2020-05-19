<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\CacheItemPool;

use Psr\Cache;
use RemotelyLiving\PHPCacheAdapter\SimpleCache;

final class ChainBuilder
{
    private SimpleCache\Chain $chain;

    private ?string $namespace;

    private ?int $defaultTTL;

    private function __construct(?string $namespace = null, int $defaultTTL = null)
    {

        $this->namespace = $namespace;
        $this->defaultTTL = $defaultTTL;
        $this->chain = SimpleCache\Chain::create();
    }

    public static function create(?string $namespace = null, int $defaultTTL = null): self
    {
        return new self($namespace, $defaultTTL);
    }

    public function addMemcached(\Memcached $memcached): self
    {
        $this->chain->addAdapter(SimpleCache\Memcached::create($memcached));

        return $this;
    }

    public function addRedis(\Redis $redis): self
    {
        $this->chain->addAdapter(SimpleCache\Redis::create($redis));

        return $this;
    }

    public function addMemory(int $maxItems = null): self
    {
        $this->chain->addAdapter(SimpleCache\Memory::create($maxItems));

        return $this;
    }

    public function addAPCu(): self
    {
        $this->chain->addAdapter(SimpleCache\APCu::create());

        return $this;
    }

    public function build(): Cache\CacheItemPoolInterface
    {
        return CacheItemPool::createFromSimpleCache($this->chain, $this->namespace, $this->defaultTTL);
    }
}
