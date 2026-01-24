<?php

namespace Tests\Unit\CircuitBreaker;

use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\CircuitBreakerConfig;
use CircuitBreaker\CircuitBreakerState;

class HalfOpenStateTest extends AbstractStateTestCase
{
    public function testChangeStateToClosed(): void
    {
        $name = __CLASS__ . __METHOD__;
        $circuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 3,
            halfOpenThreshold: 2,
        ));

        $this->reset($name);
        $this->provider->setState($name, CircuitBreakerState::HALF_OPEN);

        $run = fn() => $circuit->run(
            $name,
            function () {
                return '{"response": "data"}';
            }
        );

        $this->assertEquals(0, $this->provider->getHalfOpenAttempts($name));

        $run();
        $this->assertEquals(1, $this->provider->getHalfOpenAttempts($name));

        $run();
        $this->assertEquals(2, $this->provider->getHalfOpenAttempts($name));

        $response = $run();

        $this->assertEquals('{"response": "data"}', $response);
        $this->assertEquals(CircuitBreakerState::CLOSED, $this->provider->getState($name));
        $this->assertEquals(0, $this->provider->getFailedAttempts($name));
    }

    public function testChangeStateToOpen(): void
    {
        $name = __CLASS__ . __METHOD__;
        $circuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 3,
            halfOpenThreshold: 2
        ));

        $this->reset($name);
        $this->provider->setState($name, CircuitBreakerState::HALF_OPEN);

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
        $this->assertEquals(CircuitBreakerState::OPEN, $this->provider->getState($name));
        $this->assertEquals(0, $this->provider->getFailedAttempts($name));
    }
}
