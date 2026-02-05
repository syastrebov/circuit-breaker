<?php

namespace CircuitBreaker\Providers;

use CircuitBreaker\Enums\CircuitBreakerState;
use CircuitBreaker\Exceptions\ProviderException;

final class MemcachedProvider extends AbstractProvider
{
    public function __construct(
        private readonly \Memcached $memcached
    ) {
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

    private function buildKey(string $prefix, string $name, string $type): string
    {
        return sprintf('circuit_breaker:{%s.%s}:%s', $prefix, $name, $type);
    }

    #[\Override]
    protected function getValue(string $prefix, string $name, string $type): string|int|null
    {
        try {
            return $this->memcached->get($this->buildKey($prefix, $name, $type));
        } catch (\MemcachedException $e) {
            throw new ProviderException(previous: $e);
        }
    }

    #[\Override]
    protected function increment(string $prefix, string $name, string $type): void
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
