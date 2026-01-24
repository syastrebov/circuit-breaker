<?php

namespace Tests\Unit\CircuitBreaker\Provider;

use CircuitBreaker\CircuitBreakerState;
use CircuitBreaker\Provider\ProviderInterface;

abstract class ProviderTestCase extends \PHPUnit\Framework\TestCase
{
    protected ProviderInterface $provider;

    public function testDefaultState(): void
    {
        $this->assertEquals(
            CircuitBreakerState::CLOSED,
            $this->provider->getState(__CLASS__ . __METHOD__)
        );
    }

    public function testDefaultFailedAttempts(): void
    {
        $this->assertEquals(
            0,
            $this->provider->getFailedAttempts(__CLASS__ . __METHOD__)
        );
    }

    public function testChangeStateToOpen(): void
    {
        $name = __CLASS__ . __METHOD__;

        $this->provider->setState($name, CircuitBreakerState::OPEN);

        $this->assertEquals(CircuitBreakerState::OPEN, $this->provider->getState($name));
        $this->assertEquals(time(), $this->provider->getStateTimestamp($name));
    }

    public function testChangeStateToHalfOpen(): void
    {
        $name = __CLASS__ . __METHOD__;

        $this->provider->incrementHalfOpenAttempts($name);
        $this->provider->incrementHalfOpenAttempts($name);
        $this->provider->incrementHalfOpenAttempts($name);

        $this->provider->setState($name, CircuitBreakerState::HALF_OPEN);
        $this->assertEquals(CircuitBreakerState::HALF_OPEN, $this->provider->getState($name));
        $this->assertEquals(0, $this->provider->getHalfOpenAttempts($name));
    }

    public function testChangeStateToClosed(): void
    {
        $name = __CLASS__ . __METHOD__;

        $this->provider->incrementFailedAttempts($name);
        $this->provider->incrementFailedAttempts($name);
        $this->provider->incrementFailedAttempts($name);

        $this->provider->setState($name, CircuitBreakerState::CLOSED);
        $this->assertEquals(CircuitBreakerState::CLOSED, $this->provider->getState($name));
        $this->assertEquals(0, $this->provider->getFailedAttempts($name));
    }

    public function testIncrementAndResetFailedAttempts(): void
    {
        $name = __CLASS__ . __METHOD__;

        $this->assertEquals(0, $this->provider->getFailedAttempts($name));

        $this->provider->incrementFailedAttempts($name);
        $this->provider->incrementFailedAttempts($name);
        $this->provider->incrementFailedAttempts($name);

        $this->assertEquals(3, $this->provider->getFailedAttempts($name));

        $this->provider->incrementFailedAttempts($name);
        $this->provider->incrementFailedAttempts($name);

        $this->assertEquals(5, $this->provider->getFailedAttempts($name));
    }

    public function testIncrementAndResetHalfOpenAttempts(): void
    {
        $name = __CLASS__ . __METHOD__;

        $this->assertEquals(0, $this->provider->getHalfOpenAttempts($name));

        $this->provider->incrementHalfOpenAttempts($name);
        $this->provider->incrementHalfOpenAttempts($name);
        $this->provider->incrementHalfOpenAttempts($name);

        $this->assertEquals(3, $this->provider->getHalfOpenAttempts($name));

        $this->provider->incrementHalfOpenAttempts($name);
        $this->provider->incrementHalfOpenAttempts($name);

        $this->assertEquals(5, $this->provider->getHalfOpenAttempts($name));
    }
}
