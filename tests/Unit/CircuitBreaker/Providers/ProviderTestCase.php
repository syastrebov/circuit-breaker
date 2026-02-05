<?php

namespace Tests\Unit\CircuitBreaker\Providers;

use CircuitBreaker\Contracts\ProviderInterface;
use CircuitBreaker\Enums\CircuitBreakerState;
use Tests\Unit\CircuitBreaker\Traits\DefaultConfigTrait;

abstract class ProviderTestCase extends \PHPUnit\Framework\TestCase
{
    use DefaultConfigTrait;

    protected ProviderInterface $provider;

    public function testDefaultState(): void
    {
        $this->assertEquals(CircuitBreakerState::CLOSED, $this->getDefaultState(static::class . __METHOD__));
    }

    public function testDefaultFailedAttempts(): void
    {
        $this->assertEquals(0, $this->getDefaultFailedAttempts(static::class . __METHOD__));
    }

    public function testChangeStateToOpen(): void
    {
        $name = static::class . __METHOD__;

        $this->setDefaultState($name, CircuitBreakerState::OPEN);

        $this->assertEquals(CircuitBreakerState::OPEN, $this->getDefaultState($name));
        $this->assertEquals(time(), $this->getDefaultStateTimestamp($name));
    }

    public function testChangeStateToHalfOpen(): void
    {
        $name = static::class . __METHOD__;

        // increment to test if half open attempts are reset after changing state
        $this->incrementDefaultHalfOpenAttempts($name);
        $this->incrementDefaultHalfOpenAttempts($name);
        $this->incrementDefaultHalfOpenAttempts($name);

        $this->setDefaultState($name, CircuitBreakerState::HALF_OPEN);
        $this->assertEquals(CircuitBreakerState::HALF_OPEN, $this->getDefaultState($name));
        $this->assertEquals(0, $this->getDefaultHalfOpenAttempts($name));
    }

    public function testChangeStateToClosed(): void
    {
        $name = static::class . __METHOD__;

        // increment to test if failed attempts are reset after changing state
        $this->incrementDefaultFailedAttempts($name);
        $this->incrementDefaultFailedAttempts($name);
        $this->incrementDefaultFailedAttempts($name);

        $this->setDefaultState($name, CircuitBreakerState::CLOSED);
        $this->assertEquals(CircuitBreakerState::CLOSED, $this->getDefaultState($name));
        $this->assertEquals(0, $this->getDefaultFailedAttempts($name));
    }

    public function testIncrementAndResetFailedAttempts(): void
    {
        $name = static::class . __METHOD__;

        $this->assertEquals(0, $this->getDefaultFailedAttempts($name));

        $this->incrementDefaultFailedAttempts($name);
        $this->incrementDefaultFailedAttempts($name);
        $this->incrementDefaultFailedAttempts($name);

        $this->assertEquals(3, $this->getDefaultFailedAttempts($name));

        $this->incrementDefaultFailedAttempts($name);
        $this->incrementDefaultFailedAttempts($name);

        $this->assertEquals(5, $this->getDefaultFailedAttempts($name));
    }

    public function testIncrementAndResetHalfOpenAttempts(): void
    {
        $name = static::class . __METHOD__;

        $this->assertEquals(0, $this->getDefaultHalfOpenAttempts($name));

        $this->incrementDefaultHalfOpenAttempts($name);
        $this->incrementDefaultHalfOpenAttempts($name);
        $this->incrementDefaultHalfOpenAttempts($name);

        $this->assertEquals(3, $this->getDefaultHalfOpenAttempts($name));

        $this->incrementDefaultHalfOpenAttempts($name);
        $this->incrementDefaultHalfOpenAttempts($name);

        $this->assertEquals(5, $this->getDefaultHalfOpenAttempts($name));
    }
}
