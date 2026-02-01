<?php

namespace CircuitBreaker\Providers;

use CircuitBreaker\Enums\CircuitBreakerState;
use CircuitBreaker\Exceptions\ProviderException;

readonly class MemcachedProvider implements ProviderInterface
{
    public function __construct(
        private \Memcached $memcached
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

    public function getFailedAttempts(string $name): int
    {
        return (int) $this->getValue($name, self::KEY_FAILED_ATTEMPTS);
    }

    public function getHalfOpenAttempts(string $name): int
    {
        return (int) $this->getValue($name, self::KEY_HALF_OPEN_ATTEMPTS);
    }

    public function setState(string $name, CircuitBreakerState $state): void
    {
        try {
            $this->memcached->set($this->buildKey($name, self::KEY_STATE), $state->value);
            $this->memcached->set($this->buildKey($name, self::KEY_STATE_TIMESTAMP), time());
            $this->memcached->delete($this->buildKey($name, self::KEY_HALF_OPEN_ATTEMPTS));
            $this->memcached->delete($this->buildKey($name, self::KEY_FAILED_ATTEMPTS));
        } catch (\MemcachedException $e) {
            throw new ProviderException(previous: $e);
        }
    }

    public function incrementFailedAttempts(string $name): void
    {
        $this->increment($name, self::KEY_FAILED_ATTEMPTS);
    }

    public function incrementHalfOpenAttempts(string $name): void
    {
        $this->increment($name, self::KEY_HALF_OPEN_ATTEMPTS);
    }

    private function buildKey(string $name, string $type): string
    {
        return sprintf('circuit_breaker:{%s}:%s', $name, $type);
    }

    private function getValue(string $name, string $type): mixed
    {
        try {
            return $this->memcached->get($this->buildKey($name, $type));
        } catch (\MemcachedException $e) {
            throw new ProviderException(previous: $e);
        }
    }

    private function increment(string $name, string $type): void
    {
        try {
            $key = $this->buildKey($name, $type);

            if (!$this->memcached->add($key, 1)) {
                $this->memcached->increment($key);
            }
        } catch (\MemcachedException $e) {
            throw new ProviderException(previous: $e);
        }
    }
}
