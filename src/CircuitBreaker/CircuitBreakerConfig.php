<?php

namespace CircuitBreaker;

final readonly class CircuitBreakerConfig
{
    public const string DEFAULT_PREFIX = 'default';
    public const int DEFAULT_RETRIES = 3;
    public const int CLOSED_THRESHOLD = 3;
    public const int HALF_OPEN_THRESHOLD = 3;
    public const int RETRY_INTERVAL = 1000;
    public const int OPEN_TIMEOUT = 60;
    public const bool FALLBACK_OR_NULL = false;

    public function __construct(
        public string $prefix = self::DEFAULT_PREFIX,
        public int $retries = self::DEFAULT_RETRIES,
        public int $closedThreshold = self::CLOSED_THRESHOLD,
        public int $halfOpenThreshold = self::HALF_OPEN_THRESHOLD,
        public int $retryInterval = self::RETRY_INTERVAL,
        public int $openTimeout = self::OPEN_TIMEOUT,
        public bool $fallbackOrNull = self::FALLBACK_OR_NULL,
    ) {
        if (!$this->prefix) {
            throw new \InvalidArgumentException('Prefix must be set');
        }
        if ($this->retries < 1) {
            throw new \InvalidArgumentException('Retries must be greater than 0');
        }
        if ($this->closedThreshold < 1) {
            throw new \InvalidArgumentException('Closed threshold must be greater than 0');
        }
        if ($this->halfOpenThreshold < 1) {
            throw new \InvalidArgumentException('Half open threshold must be greater than 0');
        }
        if ($this->retryInterval < 1) {
            throw new \InvalidArgumentException('Retry interval must be greater than 0');
        }
        if ($this->openTimeout < 1) {
            throw new \InvalidArgumentException('Open timeout must be greater than 0');
        }
    }

    public static function create(array $config): self
    {
        return new self(
            prefix: $config["prefix"] ?? self::DEFAULT_PREFIX,
            retries: $config["retries"] ?? self::DEFAULT_RETRIES,
            closedThreshold: $config["closed_threshold"] ?? self::CLOSED_THRESHOLD,
            halfOpenThreshold: $config["half_open_threshold"] ?? self::HALF_OPEN_THRESHOLD,
            retryInterval: $config["retry_interval"] ?? self::RETRY_INTERVAL,
            openTimeout: $config["open_timeout"] ?? self::OPEN_TIMEOUT,
            fallbackOrNull: $config["fallback_or_null"] ?? self::FALLBACK_OR_NULL,
        );
    }
}
