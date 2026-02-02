<?php

namespace CircuitBreaker\Providers;

use CircuitBreaker\Enums\CircuitBreakerState;
use CircuitBreaker\Exceptions\ProviderException;

readonly class RedisProvider implements ProviderInterface
{
    public function __construct(
        private \Redis|\RedisCluster $redis
    ) {
    }

    public function getState(string $prefix, string $name): CircuitBreakerState
    {
        if ($state = $this->getValue($prefix, $name, self::KEY_STATE)) {
            return CircuitBreakerState::from($state);
        }

        return CircuitBreakerState::CLOSED;
    }

    public function getStateTimestamp(string $prefix, string $name): int
    {
        return (int) $this->getValue($prefix, $name, self::KEY_STATE_TIMESTAMP);
    }

    public function getHalfOpenAttempts(string $prefix, string $name): int
    {
        return (int) $this->getValue($prefix, $name, self::KEY_HALF_OPEN_ATTEMPTS);
    }

    public function getFailedAttempts(string $prefix, string $name): int
    {
        return (int) $this->getValue($prefix, $name, self::KEY_FAILED_ATTEMPTS);
    }

    public function setState(string $prefix, string $name, CircuitBreakerState $state): void
    {
        try {
            $this->redis->multi();

            $this->redis->set($this->buildKey($prefix, $name, self::KEY_STATE), $state->value);
            $this->redis->set($this->buildKey($prefix, $name, self::KEY_STATE_TIMESTAMP), time());
            $this->redis->del($this->buildKey($prefix, $name, self::KEY_HALF_OPEN_ATTEMPTS));
            $this->redis->del($this->buildKey($prefix, $name, self::KEY_FAILED_ATTEMPTS));

            $this->redis->exec();
        } catch (\RedisException $e) {
            throw new ProviderException(previous: $e);
        }
    }

    public function incrementHalfOpenAttempts(string $prefix, string $name): void
    {
        $this->increment($prefix, $name, self::KEY_HALF_OPEN_ATTEMPTS);
    }

    public function incrementFailedAttempts(string $prefix, string $name): void
    {
        $this->increment($prefix, $name, self::KEY_FAILED_ATTEMPTS);
    }

    private function buildKey(string $prefix, string $name, string $type): string
    {
        return sprintf('circuit_breaker:{%s.%s}:%s', $prefix, $name, $type);
    }

    private function getValue(string $prefix, string $name, string $type): mixed
    {
        try {
            return $this->redis->get($this->buildKey($prefix, $name, $type));
        } catch (\RedisException $e) {
            throw new ProviderException(previous: $e);
        }
    }

    private function increment(string $prefix, string $name, string $type): void
    {
        try {
            $this->redis->incr($this->buildKey($prefix, $name, $type));
        } catch (\RedisException $e) {
            throw new ProviderException(previous: $e);
        }
    }
}
