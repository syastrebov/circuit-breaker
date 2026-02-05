<?php

namespace Tests\Unit\CircuitBreaker;

use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\CircuitBreakerConfig;
use CircuitBreaker\Enums\CircuitBreakerState;
use CircuitBreaker\Exceptions\UnableToProcessException;

final class ClosedStateTest extends StateTestCase
{
    public function testSuccess(): void
    {
        $name = __CLASS__ . __METHOD__;
        $circuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 2,
            closedThreshold: 3,
        ));

        $this->resetDefaultConfig($name);

        $response = $circuit->run(
            $name,
            function () {
                return '{"response": "data"}';
            }
        );

        $this->assertEquals('{"response": "data"}', $response);
        $this->assertEquals(CircuitBreakerState::CLOSED, $this->getDefaultState($name));
        $this->assertEquals(CircuitBreakerState::CLOSED, $circuit->getState($name));
        $this->assertEquals(0, $this->getDefaultFailedAttempts($name));
        $this->assertEquals(0, $circuit->getFailedAttempts($name));
    }

    public function testRetry(): void
    {
        $name = __CLASS__ . __METHOD__;
        $circuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 3,
            closedThreshold: 4,
        ));

        $this->resetDefaultConfig($name);

        $attempt = 0;
        $response = $circuit->run(
            $name,
            function () use (&$attempt) {
                if (++$attempt < 3) {
                    throw new \RuntimeException('unable to fetch data');
                } else {
                    return '{"response": "data"}';
                }
            }
        );

        $this->assertEquals('{"response": "data"}', $response);
        $this->assertEquals(CircuitBreakerState::CLOSED, $this->getDefaultState($name));
        $this->assertEquals(CircuitBreakerState::CLOSED, $circuit->getState($name));
        $this->assertEquals(2, $this->getDefaultFailedAttempts($name));
        $this->assertEquals(2, $circuit->getFailedAttempts($name));
    }

    public function testFallback(): void
    {
        $name = __CLASS__ . __METHOD__;
        $circuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 3,
            closedThreshold: 4,
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
        $this->assertEquals(CircuitBreakerState::CLOSED, $this->getDefaultState($name));
        $this->assertEquals(CircuitBreakerState::CLOSED, $circuit->getState($name));
        $this->assertEquals(3, $this->getDefaultFailedAttempts($name));
        $this->assertEquals(3, $circuit->getFailedAttempts($name));
    }

    public function testNull(): void
    {
        $name = __CLASS__ . __METHOD__;
        $circuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 2,
            closedThreshold: 3,
            fallbackOrNull: true
        ));

        $this->resetDefaultConfig($name);

        $response = $circuit->run(
            $name,
            function () {
                throw new \RuntimeException('unable to fetch data');
            }
        );

        $this->assertNull($response);
        $this->assertEquals(CircuitBreakerState::CLOSED, $this->getDefaultState($name));
        $this->assertEquals(CircuitBreakerState::CLOSED, $circuit->getState($name));
        $this->assertEquals(2, $this->getDefaultFailedAttempts($name));
        $this->assertEquals(2, $circuit->getFailedAttempts($name));
    }

    public function testUnableToProcessException(): void
    {
        $name = __CLASS__ . __METHOD__;
        $circuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 2,
            closedThreshold: 3,
            fallbackOrNull: false
        ));

        $this->resetDefaultConfig($name);

        $this->expectException(UnableToProcessException::class);

        $circuit->run(
            $name,
            function () {
                throw new \RuntimeException('unable to fetch data');
            }
        );

        $this->assertEquals(CircuitBreakerState::CLOSED, $this->getDefaultState($name));
        $this->assertEquals(CircuitBreakerState::CLOSED, $circuit->getState($name));
        $this->assertEquals(2, $this->getDefaultFailedAttempts($name));
        $this->assertEquals(2, $circuit->getFailedAttempts($name));
    }
}
