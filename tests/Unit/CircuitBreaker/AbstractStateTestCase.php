<?php

namespace Tests\Unit\CircuitBreaker;

use CircuitBreaker\CircuitBreakerState;
use CircuitBreaker\Provider\ProviderInterface;
use CircuitBreaker\Provider\MemoryProvider;

abstract class AbstractStateTestCase extends \PHPUnit\Framework\TestCase
{
    protected ProviderInterface $provider;

    public function setUp(): void
    {
        $this->provider = new MemoryProvider();
    }

    protected function reset(string $name): void
    {
        $this->provider->setState($name, CircuitBreakerState::CLOSED);
    }
}
