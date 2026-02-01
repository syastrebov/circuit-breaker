<?php

namespace Tests\Unit\CircuitBreaker;

use CircuitBreaker\Enums\CircuitBreakerState;
use CircuitBreaker\Providers\MemoryProvider;
use CircuitBreaker\Providers\ProviderInterface;

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
