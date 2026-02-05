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

    #[\Override]
    public function getState(string $prefix, string $name): CircuitBreakerState
    {
        if ($state = $this->getValue($prefix, $name, self::KEY_STATE)) {
            return CircuitBreakerState::from($state);
        }

        return CircuitBreakerState::CLOSED;
    }

    #[\Override]
    public function getStateTimestamp(string $prefix, string $name): int
    {
        return (int) $this->getValue($prefix, $name, self::KEY_STATE_TIMESTAMP);
    }

    #[\Override]
    public function getFailedAttempts(string $prefix, string $name): int
    {
        return (int) $this->getValue($prefix, $name, self::KEY_FAILED_ATTEMPTS);
    }

    #[\Override]
    public function getHalfOpenAttempts(string $prefix, string $name): int
    {
        return (int) $this->getValue($prefix, $name, self::KEY_HALF_OPEN_ATTEMPTS);
    }

    #[\Override]
    public function setState(string $prefix, string $name, CircuitBreakerState $state): void
    {
        try {
            $this->memcached->set($this->buildKey($prefix, $name, self::KEY_STATE), $state->value);
            $this->memcached->set($this->buildKey($prefix, $name, self::KEY_STATE_TIMESTAMP), time());
            $this->memcached->delete($this->buildKey($prefix, $name, self::KEY_HALF_OPEN_ATTEMPTS));
            $this->memcached->delete($this->buildKey($prefix, $name, self::KEY_FAILED_ATTEMPTS));
        } catch (\MemcachedException $e) {
            throw new ProviderException(previous: $e);
        }
    }

    #[\Override]
    public function incrementFailedAttempts(string $prefix, string $name): void
    {
        $this->increment($prefix, $name, self::KEY_FAILED_ATTEMPTS);
    }

    #[\Override]
    public function incrementHalfOpenAttempts(string $prefix, string $name): void
    {
        $this->increment($prefix, $name, self::KEY_HALF_OPEN_ATTEMPTS);
    }

    private function buildKey(string $prefix, string $name, string $type): string
    {
        return sprintf('circuit_breaker:{%s.%s}:%s', $prefix, $name, $type);
    }

    private function getValue(string $prefix, string $name, string $type): mixed
    {
        try {
            return $this->memcached->get($this->buildKey($prefix, $name, $type));
        } catch (\MemcachedException $e) {
            throw new ProviderException(previous: $e);
        }
    }

    private function increment(string $prefix, string $name, string $type): void
    {
        try {
            $key = $this->buildKey($prefix, $name, $type);

            if (!$this->memcached->add($key, 1)) {
                $this->memcached->increment($key);
            }
        } catch (\MemcachedException $e) {
            throw new ProviderException(previous: $e);
        }
    }
}
