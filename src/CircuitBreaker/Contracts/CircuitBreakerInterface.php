<?php

namespace CircuitBreaker\Contracts;

use CircuitBreaker\CircuitBreakerConfig;
use CircuitBreaker\Enums\CircuitBreakerState;

interface CircuitBreakerInterface
{
    public function getConfig(): CircuitBreakerConfig;

    public function getState(string $name): CircuitBreakerState;

    public function getStateTimestamp(string $name): int;

    public function getFailedAttempts(string $name): int;

    public function getHalfOpenAttempts(string $name): int;

    public function run(string $name, callable $action, ?callable $fallback = null): mixed;
}
