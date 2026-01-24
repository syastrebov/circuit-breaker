<?php

namespace CircuitBreaker;

readonly class CircuitBreakerConfig
{
    public function __construct(
        public int $retries = 3,
        public int $closedThreshold = 3,
        public int $halfOpenThreshold = 3,
        public int $retryInterval = 1000,
        public int $openTimeout = 60,
        public bool $fallbackOrNull = false,
    ) {
    }
}
