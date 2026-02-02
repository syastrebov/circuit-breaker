<?php

namespace Tests\Unit\CircuitBreaker;

use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\CircuitBreakerConfig;
use CircuitBreaker\Enums\CircuitBreakerState;
use CircuitBreaker\Exceptions\UnableToProcessException;

class OpenStateTest extends StateTestCase
{
    public function testChangeStateToOpen(): void
    {
        $name = __CLASS__ . __METHOD__;
        $circuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 5,
            closedThreshold: 2
        ));

        $this->resetDefaultConfig($name);

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
        $this->assertEquals(0, $this->getDefaultFailedAttempts($name));
    }

    public function testChangeStateToHalfOpen(): void
    {
        $name = __CLASS__ . __METHOD__;
        $circuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 3,
            closedThreshold: 2,
            halfOpenThreshold: 5,
            openTimeout: 1
        ));

        $this->resetDefaultConfig($name);

        // change state to OPEN
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
        $this->assertEquals(0, $this->getDefaultFailedAttempts($name));

        // run when state is opened, nothing should be changed
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
        $this->assertEquals(0, $this->getDefaultFailedAttempts($name));

        // run after timeout, state should be changed to HALF_OPEN
        sleep(2);

        $response = $circuit->run(
            $name,
            function () {
                return '{"response": "data"}';
            }
        );

        $this->assertEquals('{"response": "data"}', $response);
        $this->assertEquals(CircuitBreakerState::HALF_OPEN, $this->getDefaultState($name));
        $this->assertEquals(0, $this->getDefaultFailedAttempts($name));
        $this->assertEquals(1, $this->getDefaultHalfOpenAttempts($name));
    }

    public function testTryToChangeStateToHalfOpenButFailed(): void
    {
        $name = __CLASS__ . __METHOD__;
        $circuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 3,
            closedThreshold: 2,
            halfOpenThreshold: 5,
            openTimeout: 1
        ));

        $this->resetDefaultConfig($name);

        // change state to OPEN
        $circuit->run(
            $name,
            function () {
                throw new \RuntimeException('unable to fetch data');
            },
            function () {
                return '{"response": "cached data"}';
            }
        );

        $this->assertEquals(CircuitBreakerState::OPEN, $this->getDefaultState($name));

        // run after timeout, state should be changed to HALF_OPEN
        sleep(2);

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
        $this->assertEquals(0, $this->getDefaultFailedAttempts($name));
        $this->assertEquals(0, $this->getDefaultHalfOpenAttempts($name));
    }

    public function testFallbackReturnsEmptyResponse(): void
    {
        $name = __CLASS__ . __METHOD__;
        $circuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 3,
            closedThreshold: 2,
            halfOpenThreshold: 5,
            openTimeout: 1
        ));

        $this->resetDefaultConfig($name);

        $this->expectException(UnableToProcessException::class);

        // change state to OPEN
        $circuit->run(
            $name,
            function () {
                throw new \RuntimeException('unable to fetch data');
            },
            function () {
                return null;
            }
        );

        $this->assertEquals(CircuitBreakerState::OPEN, $this->getDefaultState($name));
    }
}
