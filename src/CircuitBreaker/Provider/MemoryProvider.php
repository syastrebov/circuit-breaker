<?php

namespace CircuitBreaker\Provider;

use CircuitBreaker\CircuitBreakerState;

class MemoryProvider implements ProviderInterface
{
    private array $state = [];
    private array $failedAttempts = [];
    private array $halfOpenedAttempts = [];
    private array $stateTimestamps = [];

    public function getState(string $name): CircuitBreakerState
    {
        return $this->state[$name] ?? CircuitBreakerState::CLOSED;
    }

    public function getStateTimestamp(string $name): int
    {
        return $this->stateTimestamps[$name] ?? 0;
    }

    public function getFailedAttempts(string $name): int
    {
        return $this->failedAttempts[$name] ?? 0;
    }

    public function getHalfOpenAttempts(string $name): int
    {
        return $this->halfOpenedAttempts[$name] ?? 0;
    }

    public function setState(string $name, CircuitBreakerState $state): void
    {
        $this->state[$name] = $state;
        $this->failedAttempts[$name] = 0;
        $this->halfOpenedAttempts[$name] = 0;
        $this->stateTimestamps[$name] = time();
    }

    public function incrementFailedAttempts(string $name): void
    {
        $this->failedAttempts[$name] = ($this->failedAttempts[$name] ?? 0) + 1;
    }

    public function incrementHalfOpenAttempts(string $name): void
    {
        $this->halfOpenedAttempts[$name] = ($this->halfOpenedAttempts[$name] ?? 0) + 1;
    }
}
