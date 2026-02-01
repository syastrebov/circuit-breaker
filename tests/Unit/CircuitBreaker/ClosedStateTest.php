<?php

namespace Tests\Unit\CircuitBreaker;

use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\CircuitBreakerConfig;
use CircuitBreaker\Enums\CircuitBreakerState;
use CircuitBreaker\Exceptions\UnableToProcessException;

class ClosedStateTest extends AbstractStateTestCase
{
    public function testSuccess(): void
    {
        $name = __CLASS__ . __METHOD__;
        $circuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 2,
            closedThreshold: 3,
        ));

        $this->reset($name);

        $response = $circuit->run(
            $name,
            function () {
                return '{"response": "data"}';
            }
        );

        $this->assertEquals('{"response": "data"}', $response);
        $this->assertEquals(CircuitBreakerState::CLOSED, $this->provider->getState($name));
        $this->assertEquals(0, $this->provider->getFailedAttempts($name));
    }

    public function testRetry(): void
    {
        $name = __CLASS__ . __METHOD__;
        $circuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 3,
            closedThreshold: 4,
        ));

        $this->reset($name);

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
        $this->assertEquals(CircuitBreakerState::CLOSED, $this->provider->getState($name));

        // 0 because last request was successful
        $this->assertEquals(2, $this->provider->getFailedAttempts($name));
    }

    public function testFallback(): void
    {
        $name = __CLASS__ . __METHOD__;
        $circuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 3,
            closedThreshold: 4,
        ));

        $this->reset($name);

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
        $this->assertEquals(CircuitBreakerState::CLOSED, $this->provider->getState($name));
        $this->assertEquals(3, $this->provider->getFailedAttempts($name));
    }

    public function testNull(): void
    {
        $name = __CLASS__ . __METHOD__;
        $circuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 2,
            closedThreshold: 3,
            fallbackOrNull: true
        ));

        $this->reset($name);

        $response = $circuit->run(
            $name,
            function () {
                throw new \RuntimeException('unable to fetch data');
            }
        );

        $this->assertNull($response);
        $this->assertEquals(CircuitBreakerState::CLOSED, $this->provider->getState($name));
        $this->assertEquals(2, $this->provider->getFailedAttempts($name));
    }

    public function testUnableToProcessException(): void
    {
        $name = __CLASS__ . __METHOD__;
        $circuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 2,
            closedThreshold: 3,
            fallbackOrNull: false
        ));

        $this->reset($name);

        $this->expectException(UnableToProcessException::class);

        $circuit->run(
            $name,
            function () {
                throw new \RuntimeException('unable to fetch data');
            }
        );

        $this->assertEquals(CircuitBreakerState::CLOSED, $this->provider->getState($name));
        $this->assertEquals(2, $this->provider->getFailedAttempts($name));
    }
}
