<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\SimpleCache;

use Psr\SimpleCache;
use RemotelyLiving\PHPCacheAdapter\Assertions;

final class Memcached extends AbstractAdapter
{
    private \Memcached $memcached;

    private function __construct(\Memcached $memcached)
    {
        Assertions::assertExtensionLoaded('memcached');
        $this->memcached = $memcached;
    }

    public static function create(\Memcached $memcached): SimpleCache\CacheInterface
    {
        return new self($memcached);
    }

    protected function flush(): bool
    {
        return $this->memcached->flush();
    }

    protected function exists(string $key): bool
    {
        return $this->memcached->get($key) !== false;
    }

    /**
     * @inheritDoc
     */
    protected function multiGet(array $keys, $default = null): \Generator
    {
        foreach ($this->memcached->getMulti($keys, \Memcached::GET_PRESERVE_ORDER) as $key => $value) {
            yield $key => $value ?? $default;
        }
    }

    protected function multiDelete(array $keys): bool
    {
        return count($this->memcached->deleteMulti($keys)) === count($keys);
    }

    protected function multiSave(array $values, int $ttl = null): bool
    {
        return $this->memcached->setMulti($values, (int) $ttl);
    }
}
