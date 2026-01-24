<?php

namespace CircuitBreaker\Provider;

use CircuitBreaker\CircuitBreakerState;

interface ProviderInterface
{
    public const string KEY_STATE = 'state';
    public const string KEY_STATE_TIMESTAMP = 'state_timestamp';
    public const string KEY_HALF_OPEN_ATTEMPTS = 'half_open_attempts';
    public const string KEY_FAILED_ATTEMPTS = 'failed_attempts';

    public function getState(string $name): CircuitBreakerState;

    public function getStateTimestamp(string $name): int;

    public function getFailedAttempts(string $name): int;

    public function getHalfOpenAttempts(string $name): int;

    public function setState(string $name, CircuitBreakerState $state): void;

    public function incrementFailedAttempts(string $name): void;

    public function incrementHalfOpenAttempts(string $name): void;
}
