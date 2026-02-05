<?php

namespace Tests\Unit\CircuitBreaker;

use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\CircuitBreakerConfig;
use CircuitBreaker\Enums\CircuitBreakerState;
use Tests\Unit\CircuitBreaker\Traits\CustomConfigTrait;
use Tests\Unit\CircuitBreaker\Traits\DefaultConfigTrait;

final class PrefixTest extends StateTestCase
{
    use DefaultConfigTrait;
    use CustomConfigTrait;

    public function testStateChange(): void
    {
        $name = __CLASS__ . __METHOD__;

        $defaultCircuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 2,
            closedThreshold: 1,
        ));

        $response = $defaultCircuit->run(
            $name,
            function () {
                throw new \RuntimeException('unable to fetch data');
            },
            function () {
                return '{"response": "default data"}';
            }
        );

        $this->assertEquals('{"response": "default data"}', $response);
        $this->assertEquals(CircuitBreakerState::OPEN, $this->getDefaultState($name));
        $this->assertEquals(CircuitBreakerState::OPEN, $defaultCircuit->getState($name));

        $customCircuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            prefix: self::CUSTOM_PREFIX,
            retries: 2,
            closedThreshold: 1,
        ));

        $response = $customCircuit->run(
            $name,
            function () {
                return '{"response": "data"}';
            },
            function () {
                return '{"response": "cached data"}';
            }
        );

        $this->assertEquals('{"response": "data"}', $response);
        $this->assertEquals(CircuitBreakerState::CLOSED, $this->getCustomState($name));
        $this->assertEquals(CircuitBreakerState::CLOSED, $customCircuit->getState($name));
    }

    public function testFailedAttempts(): void
    {
        $name = __CLASS__ . __METHOD__;

        $defaultCircuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            retries: 2,
            closedThreshold: 4,
        ));

        $response = $defaultCircuit->run(
            $name,
            function () {
                throw new \RuntimeException('unable to fetch data');
            },
            function () {
                return '{"response": "default data"}';
            }
        );

        $this->assertEquals('{"response": "default data"}', $response);
        $this->assertEquals(CircuitBreakerState::CLOSED, $this->getDefaultState($name));
        $this->assertEquals(CircuitBreakerState::CLOSED, $defaultCircuit->getState($name));
        $this->assertEquals(2, $this->getDefaultFailedAttempts($name));
        $this->assertEquals(2, $defaultCircuit->getFailedAttempts($name));

        $customCircuit = new CircuitBreaker($this->provider, new CircuitBreakerConfig(
            prefix: self::CUSTOM_PREFIX,
            retries: 3,
            closedThreshold: 6,
        ));

        $response = $customCircuit->run(
            $name,
            function () {
                throw new \RuntimeException('unable to fetch data');
            },
            function () {
                return '{"response": "cached data"}';
            }
        );

        $this->assertEquals('{"response": "cached data"}', $response);
        $this->assertEquals(CircuitBreakerState::CLOSED, $customCircuit->getState($name));
        $this->assertEquals(CircuitBreakerState::CLOSED, $this->getCustomState($name));
        $this->assertEquals(3, $customCircuit->getFailedAttempts($name));
        $this->assertEquals(3, $this->getCustomFailedAttempts($name));
    }
}
