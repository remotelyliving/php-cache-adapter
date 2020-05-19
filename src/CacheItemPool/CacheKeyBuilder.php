<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\CacheItemPool;

use RemotelyLiving\PHPCacheAdapter\Assertions;

final class CacheKeyBuilder
{
    private string $namespace = '';

    private int $namespaceLength;

    private function __construct(?string $namespace = null)
    {
        if ($namespace) {
            $this->namespace = $namespace . ':';
        }

        $this->namespaceLength = \mb_strlen($this->namespace);
    }

    public static function create(?string $namespace = null): self
    {
        return new self($namespace);
    }

    /**
     * @param mixed $key
     *
     * @return string
     */
    public function buildKey($key): string
    {
        Assertions::assertValidKey($key);
        return "{$this->namespace}{$key}";
    }

    public function removeNamespace(string $key): string
    {
        return \mb_substr($key, $this->namespaceLength);
    }

    public function buildKeys(array $keys): array
    {
        $prefixed = [];
        foreach ($keys as $key) {
            $prefixed[] = $this->buildKey($key);
        }

        return $prefixed;
    }
}
