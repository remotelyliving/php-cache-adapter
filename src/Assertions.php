<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter;

use RemotelyLiving\PHPCacheAdapter\Exceptions;

final class Assertions
{
    /**
     * @param mixed $ttl
     *
     * @throws \RemotelyLiving\PHPCacheAdapter\Exceptions\InvalidArgument
     */
    public static function assertValidTTL($ttl): void
    {
        if (is_null($ttl) || is_int($ttl) || (is_object($ttl) && $ttl instanceof \DateTime)) {
            return;
        }

        throw Exceptions\InvalidArgument::invalidTTL();
    }

    /**
     * @param mixed $key
     *
     * @throws \RemotelyLiving\PHPCacheAdapter\Exceptions\InvalidArgument
     */
    public static function assertValidKey($key): void
    {
        if (!is_string($key) || preg_match('/\s/im', $key)) {
            throw Exceptions\InvalidArgument::invalidKey($key);
        }
    }

    /**
     * @param mixed $keys
     *
     * @throws \RemotelyLiving\PHPCacheAdapter\Exceptions\InvalidArgument
     */
    public static function assertValidKeys($keys): void
    {
        self::assertIterable($keys);

        foreach ($keys as $key) {
            self::assertValidKey($key);
        }
    }

    /**
     * @param mixed $iterable
     *
     * @throws \RemotelyLiving\PHPCacheAdapter\Exceptions\InvalidArgument
     */
    public static function assertIterable($iterable): void
    {
        if (!is_iterable($iterable)) {
            throw Exceptions\InvalidArgument::invalidIterable();
        }
    }

    /**
     * @throws \RemotelyLiving\PHPCacheAdapter\Exceptions\RuntimeError
     */
    public static function assertExtensionLoaded(string $extension): void
    {
        if (!extension_loaded($extension)) {
            throw Exceptions\RuntimeError::extensionNotLoaded($extension);
        }
    }
}
