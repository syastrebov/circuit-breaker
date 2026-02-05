<?php

namespace CircuitBreaker\Providers;

use CircuitBreaker\Enums\CircuitBreakerState;
use CircuitBreaker\Exceptions\ProviderException;
use Predis\Client;
use Predis\PredisException;

final class PredisProvider extends AbstractRedisProvider
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    #[\Override]
    public function setState(string $prefix, string $name, CircuitBreakerState $state): void
    {
        try {
            $this->client->transaction(function () use ($prefix, $name, $state): void {
                $this->client->set($this->buildKey($prefix, $name, self::KEY_STATE), $state->value);
                $this->client->set($this->buildKey($prefix, $name, self::KEY_STATE_TIMESTAMP), (string) time());
                $this->client->del($this->buildKey($prefix, $name, self::KEY_HALF_OPEN_ATTEMPTS));
                $this->client->del($this->buildKey($prefix, $name, self::KEY_FAILED_ATTEMPTS));
            });
        } catch (PredisException $e) {
            throw new ProviderException(previous: $e);
        }
    }

    #[\Override]
    protected function getValue(string $prefix, string $name, string $type): string|int|null
    {
        try {
            return $this->client->get($this->buildKey($prefix, $name, $type));
        } catch (PredisException $e) {
            throw new ProviderException(previous: $e);
        }
    }

    #[\Override]
    protected function increment(string $prefix, string $name, string $type): void
    {
        try {
            $this->client->incr($this->buildKey($prefix, $name, $type));
        } catch (PredisException $e) {
            throw new ProviderException(previous: $e);
        }
    }
}
