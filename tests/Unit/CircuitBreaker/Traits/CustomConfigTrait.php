<?php

namespace Tests\Unit\CircuitBreaker\Traits;

use CircuitBreaker\CircuitBreakerConfig;
use CircuitBreaker\Enums\CircuitBreakerState;
use CircuitBreaker\Providers\ProviderInterface;

/**
 * @property ProviderInterface $provider
 */
trait CustomConfigTrait
{
    public const string CUSTOM_PREFIX = 'custom';

    protected function resetCustomConfig(string $name): void
    {
        $this->provider->setState(self::CUSTOM_PREFIX, $name, CircuitBreakerState::CLOSED);
    }

    protected function setCustomState(string $name, CircuitBreakerState $state): void
    {
        $this->provider->setState(self::CUSTOM_PREFIX, $name, $state);
    }

    protected function getCustomState(string $name): CircuitBreakerState
    {
        return $this->provider->getState(self::CUSTOM_PREFIX, $name);
    }

    protected function getCustomStateTimestamp(string $name): int
    {
        return $this->provider->getStateTimestamp(self::CUSTOM_PREFIX, $name);
    }

    protected function getCustomFailedAttempts(string $name): int
    {
        return $this->provider->getFailedAttempts(self::CUSTOM_PREFIX, $name);
    }

    protected function getCustomHalfOpenAttempts(string $name): int
    {
        return $this->provider->getHalfOpenAttempts(self::CUSTOM_PREFIX, $name);
    }

    protected function incrementCustomFailedAttempts(string $name): void
    {
        $this->provider->incrementFailedAttempts(self::CUSTOM_PREFIX, $name);
    }

    protected function incrementCustomHalfOpenAttempts(string $name): void
    {
        $this->provider->incrementHalfOpenAttempts(self::CUSTOM_PREFIX, $name);
    }
}
