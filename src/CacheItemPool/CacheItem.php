<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\CacheItemPool;

use Psr\Cache;
use RemotelyLiving\PHPCacheAdapter\Exceptions;

final class CacheItem implements Cache\CacheItemInterface, \Serializable
{
    private string $key;

    /**
     * @var mixed
     */
    private $value = null;

    private bool $isHit = false;

    private ?int $ttl = null;

    private function __construct()
    {
    }

    public static function create(string $key): self
    {
        $instance = new self();
        $instance->key = $key;

        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @inheritDoc
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function isHit(): bool
    {
        return $this->isHit;
    }

    public function setAsHit(): self
    {
        $this->isHit = true;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function set($value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expiresAt($expiration): self
    {
        if ($expiration === null) {
            $this->ttl = null;
            return $this;
        }

        if (!is_object($expiration) || !($expiration instanceof \DateTimeInterface)) {
            throw Exceptions\InvalidArgument::invalidTTL();
        }

        $this->ttl = max(0, $expiration->getTimestamp() - time());

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expiresAfter($time): self
    {
        if ($time === null) {
            return $this;
        }

        if (is_int($time)) {
            $this->ttl = (int) $time;
            return $this;
        }

        if (is_object($time) && $time instanceof \DateTimeInterface) {
            return $this->expiresAt($time);
        }

        throw Exceptions\InvalidArgument::invalidTTL();
    }

    public function getTTL(): ?int
    {
        return $this->ttl;
    }

    public function unserialize($serialized, array $options = [])
    {
        $unserialized = \unserialize($serialized, $options);
        $this->key = (string) $unserialized['key'];
        $this->value = $unserialized['value'] ?? null;
        $this->ttl = $unserialized['ttl'] ?? null;
    }

    public function serialize(): string
    {
        return \serialize([
          'key' => $this->key,
          'value' => $this->value,
          'ttl' => $this->ttl
        ]);
    }
}
