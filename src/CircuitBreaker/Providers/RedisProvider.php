<?php

namespace CircuitBreaker\Providers;

use CircuitBreaker\Enums\CircuitBreakerState;
use CircuitBreaker\Exceptions\ProviderException;

final class RedisProvider extends AbstractProvider
{
    public function __construct(
        private readonly \Redis|\RedisCluster $redis
    ) {
    }

    #[\Override]
    public function setState(string $prefix, string $name, CircuitBreakerState $state): void
    {
        try {
            $this->redis->multi();

            $this->redis->set($this->buildKey($prefix, $name, self::KEY_STATE), $state->value);
            $this->redis->set($this->buildKey($prefix, $name, self::KEY_STATE_TIMESTAMP), (string) time());
            $this->redis->del($this->buildKey($prefix, $name, self::KEY_HALF_OPEN_ATTEMPTS));
            $this->redis->del($this->buildKey($prefix, $name, self::KEY_FAILED_ATTEMPTS));

            $this->redis->exec();
        } catch (\RedisException $e) {
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
            return $this->redis->get($this->buildKey($prefix, $name, $type));
        } catch (\RedisException $e) {
            throw new ProviderException(previous: $e);
        }
    }

    #[\Override]
    protected function increment(string $prefix, string $name, string $type): void
    {
        try {
            $this->redis->incr($this->buildKey($prefix, $name, $type));
        } catch (\RedisException $e) {
            throw new ProviderException(previous: $e);
        }
    }
}
