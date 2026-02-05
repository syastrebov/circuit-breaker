<?php

namespace CircuitBreaker\Providers;

abstract class AbstractRedisProvider extends AbstractProvider
{
    protected function buildKey(string $prefix, string $name, string $type): string
    {
        return sprintf('circuit_breaker:{%s.%s}:%s', $prefix, $name, $type);
    }
}
