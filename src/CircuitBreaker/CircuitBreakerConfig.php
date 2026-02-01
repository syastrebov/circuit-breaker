<?php

namespace CircuitBreaker;

readonly class CircuitBreakerConfig
{
    public const int DEFAULT_RETRIES = 3;
    public const int CLOSED_THRESHOLD = 3;
    public const int HALF_OPEN_THRESHOLD = 3;
    public const int RETRY_INTERVAL = 1000;
    public const int OPEN_TIMEOUT = 60;
    public const bool FALLBACK_OR_NULL = false;

    public function __construct(
        public int $retries = self::DEFAULT_RETRIES,
        public int $closedThreshold = self::CLOSED_THRESHOLD,
        public int $halfOpenThreshold = self::HALF_OPEN_THRESHOLD,
        public int $retryInterval = self::RETRY_INTERVAL,
        public int $openTimeout = self::OPEN_TIMEOUT,
        public bool $fallbackOrNull = self::FALLBACK_OR_NULL,
    ) {
    }

    public static function create(array $config): self
    {
        return new self(
            retries: $config["retries"] ?? self::DEFAULT_RETRIES,
            closedThreshold: $config["closed_threshold"] ?? self::CLOSED_THRESHOLD,
            halfOpenThreshold: $config["half_open_threshold"] ?? self::HALF_OPEN_THRESHOLD,
            retryInterval: $config["retry_interval"] ?? self::RETRY_INTERVAL,
            openTimeout: $config["open_timeout"] ?? self::OPEN_TIMEOUT,
            fallbackOrNull: $config["fallback_or_null"] ?? self::FALLBACK_OR_NULL,
        );
    }
}
