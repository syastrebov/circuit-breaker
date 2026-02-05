<?php

namespace Tests\Unit\CircuitBreaker\Traits;

use CircuitBreaker\CircuitBreakerConfig;
use CircuitBreaker\Contracts\ProviderInterface;
use CircuitBreaker\Enums\CircuitBreakerState;

/**
 * @property ProviderInterface $provider
 */
trait DefaultConfigTrait
{
    protected function resetDefaultConfig(string $name): void
    {
        $this->provider->setState(CircuitBreakerConfig::DEFAULT_PREFIX, $name, CircuitBreakerState::CLOSED);
    }

    protected function setDefaultState(string $name, CircuitBreakerState $state): void
    {
        $this->provider->setState(CircuitBreakerConfig::DEFAULT_PREFIX, $name, $state);
    }

    protected function getDefaultState(string $name): CircuitBreakerState
    {
        return $this->provider->getState(CircuitBreakerConfig::DEFAULT_PREFIX, $name);
    }

    protected function getDefaultStateTimestamp(string $name): int
    {
        return $this->provider->getStateTimestamp(CircuitBreakerConfig::DEFAULT_PREFIX, $name);
    }

    protected function getDefaultFailedAttempts(string $name): int
    {
        return $this->provider->getFailedAttempts(CircuitBreakerConfig::DEFAULT_PREFIX, $name);
    }

    protected function getDefaultHalfOpenAttempts(string $name): int
    {
        return $this->provider->getHalfOpenAttempts(CircuitBreakerConfig::DEFAULT_PREFIX, $name);
    }

    protected function incrementDefaultFailedAttempts(string $name): void
    {
        $this->provider->incrementFailedAttempts(CircuitBreakerConfig::DEFAULT_PREFIX, $name);
    }

    protected function incrementDefaultHalfOpenAttempts(string $name): void
    {
        $this->provider->incrementHalfOpenAttempts(CircuitBreakerConfig::DEFAULT_PREFIX, $name);
    }
}
