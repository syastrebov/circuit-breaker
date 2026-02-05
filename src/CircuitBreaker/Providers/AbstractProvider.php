<?php

namespace CircuitBreaker\Providers;

use CircuitBreaker\Contracts\ProviderInterface;
use CircuitBreaker\Enums\CircuitBreakerState;

abstract class AbstractProvider implements ProviderInterface
{
    #[\Override]
    public function getState(string $prefix, string $name): CircuitBreakerState
    {
        $state = $this->getValue($prefix, $name, self::KEY_STATE);

        return is_string($state) && in_array($state, CircuitBreakerState::getValues())
            ? CircuitBreakerState::from($state)
            : CircuitBreakerState::CLOSED;
    }

    #[\Override]
    public function getStateTimestamp(string $prefix, string $name): int
    {
        return (int) $this->getValue($prefix, $name, self::KEY_STATE_TIMESTAMP);
    }

    #[\Override]
    public function getHalfOpenAttempts(string $prefix, string $name): int
    {
        return (int) $this->getValue($prefix, $name, self::KEY_HALF_OPEN_ATTEMPTS);
    }

    #[\Override]
    public function getFailedAttempts(string $prefix, string $name): int
    {
        return (int) $this->getValue($prefix, $name, self::KEY_FAILED_ATTEMPTS);
    }

    #[\Override]
    public function incrementHalfOpenAttempts(string $prefix, string $name): void
    {
        $this->increment($prefix, $name, self::KEY_HALF_OPEN_ATTEMPTS);
    }

    #[\Override]
    public function incrementFailedAttempts(string $prefix, string $name): void
    {
        $this->increment($prefix, $name, self::KEY_FAILED_ATTEMPTS);
    }

    abstract protected function getValue(string $prefix, string $name, string $type): string|int|null;

    abstract protected function increment(string $prefix, string $name, string $type): void;
}
