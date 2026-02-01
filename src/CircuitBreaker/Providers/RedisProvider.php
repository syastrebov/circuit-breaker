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

    public function getState(string $name): CircuitBreakerState
    {
        if ($state = $this->getValue($name, self::KEY_STATE)) {
            return CircuitBreakerState::from($state);
        }

        return CircuitBreakerState::CLOSED;
    }

    public function getStateTimestamp(string $name): int
    {
        return (int) $this->getValue($name, self::KEY_STATE_TIMESTAMP);
    }

    public function getHalfOpenAttempts(string $name): int
    {
        return (int) $this->getValue($name, self::KEY_HALF_OPEN_ATTEMPTS);
    }

    public function getFailedAttempts(string $name): int
    {
        return (int) $this->getValue($name, self::KEY_FAILED_ATTEMPTS);
    }

    public function setState(string $name, CircuitBreakerState $state): void
    {
        try {
            $this->redis->multi();

            $this->redis->set($this->buildKey($name, self::KEY_STATE), $state->value);
            $this->redis->set($this->buildKey($name, self::KEY_STATE_TIMESTAMP), time());
            $this->redis->del($this->buildKey($name, self::KEY_HALF_OPEN_ATTEMPTS));
            $this->redis->del($this->buildKey($name, self::KEY_FAILED_ATTEMPTS));

            $this->redis->exec();
        } catch (\RedisException $e) {
            throw new ProviderException(previous: $e);
        }
    }

    public function incrementHalfOpenAttempts(string $name): void
    {
        $this->increment($name, self::KEY_HALF_OPEN_ATTEMPTS);
    }

    public function incrementFailedAttempts(string $name): void
    {
        $this->increment($name, self::KEY_FAILED_ATTEMPTS);
    }

    private function buildKey(string $name, string $type): string
    {
        return sprintf('circuit_breaker:{%s}:%s', $name, $type);
    }

    private function getValue(string $name, string $type): mixed
    {
        try {
            return $this->redis->get($this->buildKey($name, $type));
        } catch (\RedisException $e) {
            throw new ProviderException(previous: $e);
        }
    }

    private function increment(string $name, string $type): void
    {
        try {
            $this->redis->incr($this->buildKey($name, $type));
        } catch (\RedisException $e) {
            throw new ProviderException(previous: $e);
        }
    }
}
