<?php

namespace Tests\Unit\CircuitBreaker;

use CircuitBreaker\CircuitBreakerConfig;

final class CircuitBreakerConfigTest extends \PHPUnit\Framework\TestCase
{
    public function testEmptyPrefixException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Prefix must be set');

        new CircuitBreakerConfig(
            prefix: ''
        );
    }

    public function testRetriesException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Retries must be greater than 0');

        new CircuitBreakerConfig(
            retries: 0
        );
    }

    public function testClosedThresholdException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Closed threshold must be greater than 0');

        new CircuitBreakerConfig(
            closedThreshold: 0
        );
    }

    public function testHalfOpenThresholdException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Half open threshold must be greater than 0');

        new CircuitBreakerConfig(
            halfOpenThreshold: 0
        );
    }

    public function testRetryIntervalException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Retry interval must be greater than 0');

        new CircuitBreakerConfig(
            retryInterval: 0
        );
    }

    public function testOpenTimeoutException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Open timeout must be greater than 0');

        new CircuitBreakerConfig(
            openTimeout: 0
        );
    }
}
