<?php

namespace CircuitBreaker\Providers;

use CircuitBreaker\Enums\CircuitBreakerState;

class MemoryProvider implements ProviderInterface
{
    private array $state = [];

    public function getState(string $prefix, string $name): CircuitBreakerState
    {
        return $this->state[$prefix][$name][self::KEY_STATE] ?? CircuitBreakerState::CLOSED;
    }

    public function getStateTimestamp(string $prefix, string $name): int
    {
        return $this->state[$prefix][$name][self::KEY_STATE_TIMESTAMP] ?? 0;
    }

    public function getFailedAttempts(string $prefix, string $name): int
    {
        return $this->state[$prefix][$name][self::KEY_FAILED_ATTEMPTS] ?? 0;
    }

    public function getHalfOpenAttempts(string $prefix, string $name): int
    {
        return $this->state[$prefix][$name][self::KEY_HALF_OPEN_ATTEMPTS] ?? 0;
    }

    public function setState(string $prefix, string $name, CircuitBreakerState $state): void
    {
        $this->state[$prefix][$name] = [
            self::KEY_STATE => $state,
            self::KEY_STATE_TIMESTAMP => time(),
            self::KEY_FAILED_ATTEMPTS => 0,
            self::KEY_HALF_OPEN_ATTEMPTS => 0,
        ];
    }

    public function incrementFailedAttempts(string $prefix, string $name): void
    {
        $this->state[$prefix][$name][self::KEY_FAILED_ATTEMPTS] =
            ($this->state[$prefix][$name][self::KEY_FAILED_ATTEMPTS] ?? 0) + 1;
    }

    public function incrementHalfOpenAttempts(string $prefix, string $name): void
    {
        $this->state[$prefix][$name][self::KEY_HALF_OPEN_ATTEMPTS] =
            ($this->state[$prefix][$name][self::KEY_HALF_OPEN_ATTEMPTS] ?? 0) + 1;
    }
}
