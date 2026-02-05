<?php

namespace Tests\Unit\CircuitBreaker;

use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\CircuitBreakerConfig;
use CircuitBreaker\Enums\CircuitBreakerState;

final class HalfOpenStateTest extends StateTestCase
{
    public function testChangeStateToClosed(): void
    {
        $name = __CLASS__ . __METHOD__;
        $circuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 3,
            halfOpenThreshold: 2,
        ));

        $this->resetDefaultConfig($name);
        $this->setDefaultState($name, CircuitBreakerState::HALF_OPEN);

        $run = fn() => $circuit->run(
            $name,
            function () {
                return '{"response": "data"}';
            }
        );

        $this->assertEquals(0, $this->getDefaultHalfOpenAttempts($name));
        $this->assertEquals(0, $circuit->getHalfOpenAttempts($name));

        $run();
        $this->assertEquals(1, $this->getDefaultHalfOpenAttempts($name));
        $this->assertEquals(1, $circuit->getHalfOpenAttempts($name));

        $run();
        $this->assertEquals(2, $this->getDefaultHalfOpenAttempts($name));
        $this->assertEquals(2, $circuit->getHalfOpenAttempts($name));

        $response = $run();
        $this->assertEquals('{"response": "data"}', $response);
        $this->assertEquals(CircuitBreakerState::CLOSED, $this->getDefaultState($name));
        $this->assertEquals(CircuitBreakerState::CLOSED, $circuit->getState($name));
        $this->assertEquals(0, $this->getDefaultFailedAttempts($name));
        $this->assertEquals(0, $circuit->getHalfOpenAttempts($name));
    }

    public function testChangeStateToOpen(): void
    {
        $name = __CLASS__ . __METHOD__;
        $circuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 3,
            halfOpenThreshold: 2
        ));

        $this->resetDefaultConfig($name);
        $this->setDefaultState($name, CircuitBreakerState::HALF_OPEN);

        $response = $circuit->run(
            $name,
            function () {
                throw new \RuntimeException('unable to fetch data');
            },
            function () {
                return '{"response": "cached data"}';
            }
        );

        $this->assertEquals('{"response": "cached data"}', $response);
        $this->assertEquals(CircuitBreakerState::OPEN, $this->getDefaultState($name));
        $this->assertEquals(CircuitBreakerState::OPEN, $circuit->getState($name));
        $this->assertEquals(0, $this->getDefaultFailedAttempts($name));
        $this->assertEquals(0, $circuit->getFailedAttempts($name));
    }
}
