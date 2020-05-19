<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\Exceptions;

use Psr\Cache\InvalidArgumentException as CInvalidArg;
use Psr\SimpleCache\InvalidArgumentException as SCInvalidArg;

final class InvalidArgument extends RuntimeError implements CInvalidArg, SCInvalidArg
{
    private function __construct(string $message = '', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(
            $message,
            $code,
            $previous
        );
    }

    /**
     * @param mixed $key
     */
    public static function invalidKey($key): self
    {
        return new self(var_export($key, true) . ' is not a valid cache key');
    }

    public static function invalidTTL(): self
    {
        return new self('TTL must be an integer or \DateTime');
    }

    public static function invalidIterable(): self
    {
        return new self('Value must be iterable');
    }
}
