<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\SimpleCache;

use Psr\SimpleCache;
use RemotelyLiving\PHPCacheAdapter\Assertions;

final class Redis extends AbstractAdapter
{
    private \Redis $redis;

    private function __construct(\Redis $redis)
    {
        Assertions::assertExtensionLoaded('redis');
        $this->redis = $redis;
    }

    public static function create(\Redis $redis): SimpleCache\CacheInterface
    {
        return new self($redis);
    }

    public function flush(): bool
    {
        return $this->redis->flushAll();
    }

    /**
     * @inheritDoc
     */
    protected function multiGet(array $keys, $default = null): \Generator
    {
        $values = $this->redis->mget($keys);
        foreach ($keys as $index => $key) {
            yield $key => ($values[$index] !== false) ? \unserialize($values[$index]) : $default;
        }
    }

    protected function multiSave(array $values, int $ttl = null): bool
    {
        $multi = $this->redis->multi(\Redis::PIPELINE);
        foreach ($values as $key => $value) {
            $multi->set($key, \serialize($value), $ttl ?? $this->getDefaultTTLSeconds());
        }

        return !empty($this->redis->exec());
    }

    protected function multiDelete(array $keys): bool
    {
        return $this->redis->del($keys) === count($keys);
    }

    protected function exists(string $key): bool
    {
        return (bool) $this->redis->exists($key);
    }
}
