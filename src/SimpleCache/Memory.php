<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\SimpleCache;

use Psr\SimpleCache;

final class Memory extends AbstractAdapter
{
    private array $cache = [];

    private ?int $maxItems;

    private function __construct(int $maxItems = null)
    {
        $this->maxItems = $maxItems;
    }

    public static function create(int $maxItems = null): SimpleCache\CacheInterface
    {
        return new self($maxItems);
    }

    protected function flush(): bool
    {
        $this->cache = [];
        return empty($this->cache);
    }

    protected function exists(string $key): bool
    {
        $this->expireEntries();
        return isset($this->cache[$key]);
    }

    /**
     * @inheritDoc
     */
    protected function multiGet(array $keys, $default = null): \Generator
    {
        $this->expireEntries();
        foreach ($keys as $key) {
            yield $key => $this->cache[$key]['value'] ?? $default;
        }
    }

    protected function multiDelete(array $keys): bool
    {
        $this->expireEntries();
        foreach ($keys as $key) {
            unset($this->cache[$key]);
        }

        return true;
    }

    protected function multiSave(array $values, int $ttl = null): bool
    {
        $this->expireEntries();
        $ttl = $ttl ?? $this->getDefaultTTLSeconds();
        foreach ($values as $key => $value) {
            $this->cache[$key]['value'] = $value;
            $this->cache[$key]['expiresAt'] = $this->getExpiresFromTTL($ttl);
            if ($this->isAtEvictionThreshold()) {
                $this->evict();
                ;
            }
        }

        return true;
    }

    protected function timestamp(): int
    {
        return time();
    }

    private function expireEntries(): void
    {
        $time = $this->timestamp();
        foreach ($this->cache as $key => $entry) {
            if ($entry['expiresAt'] === null || $entry['expiresAt'] > $time) {
                continue;
            }

            unset($this->cache[$key]);
        }
    }

    private function getExpiresFromTTL(?int $ttl): ?int
    {
        if ($ttl === null) {
            return null;
        }

        return $this->timestamp() + $ttl;
    }

    private function isAtEvictionThreshold(): bool
    {
        if ($this->maxItems === null) {
            return false;
        }

        return (count($this->cache) > $this->maxItems);
    }

    private function evict(): void
    {
        array_shift($this->cache);
    }
}
