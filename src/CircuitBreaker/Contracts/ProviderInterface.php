<?php

namespace CircuitBreaker\Contracts;

use CircuitBreaker\Enums\CircuitBreakerState;

interface ProviderInterface
{
    public const string KEY_STATE = 'state';
    public const string KEY_STATE_TIMESTAMP = 'state_timestamp';
    public const string KEY_HALF_OPEN_ATTEMPTS = 'half_open_attempts';
    public const string KEY_FAILED_ATTEMPTS = 'failed_attempts';

    public function getState(string $prefix, string $name): CircuitBreakerState;

    public function getStateTimestamp(string $prefix, string $name): int;

    public function getFailedAttempts(string $prefix, string $name): int;

    public function getHalfOpenAttempts(string $prefix, string $name): int;

    public function setState(string $prefix, string $name, CircuitBreakerState $state): void;

    public function incrementFailedAttempts(string $prefix, string $name): void;

    public function incrementHalfOpenAttempts(string $prefix, string $name): void;
}
