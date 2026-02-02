<?php

namespace CircuitBreaker\Contracts;

use CircuitBreaker\CircuitBreakerConfig;

interface CircuitBreakerInterface
{
    public function getConfig(): CircuitBreakerConfig;

    public function run(string $name, callable $action, ?callable $fallback = null): mixed;
}
