<?php

namespace CircuitBreaker\Contracts;

interface CircuitBreakerInterface
{
    public function run(string $name, callable $action, ?callable $fallback = null): mixed;
}
