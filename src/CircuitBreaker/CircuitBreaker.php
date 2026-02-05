<?php

namespace CircuitBreaker;

use CircuitBreaker\Contracts\CircuitBreakerInterface;
use CircuitBreaker\Contracts\ProviderInterface;
use CircuitBreaker\Enums\CircuitBreakerState;
use CircuitBreaker\Exceptions\FailedRequestException;
use CircuitBreaker\Exceptions\ProviderException;
use CircuitBreaker\Exceptions\UnableToProcessException;
use CircuitBreaker\Exceptions\UseFallbackException;
use Psr\Log\LoggerInterface;

final class CircuitBreaker implements CircuitBreakerInterface
{
    private CircuitBreakerConfig $config;

    public function __construct(
        private readonly ProviderInterface $provider,
        ?CircuitBreakerConfig $config = null,
        private readonly ?LoggerInterface $logger = null
    ) {
        $this->config = $config ?: new CircuitBreakerConfig();
    }

    #[\Override]
    public function getConfig(): CircuitBreakerConfig
    {
        return $this->config;
    }

    #[\Override]
    public function getState(string $name): CircuitBreakerState
    {
        return $this->provider->getState($this->config->prefix, $name);
    }

    #[\Override]
    public function getStateTimestamp(string $name): int
    {
        return $this->provider->getStateTimestamp($this->config->prefix, $name);
    }

    #[\Override]
    public function getFailedAttempts(string $name): int
    {
        return $this->provider->getFailedAttempts($this->config->prefix, $name);
    }

    #[\Override]
    public function getHalfOpenAttempts(string $name): int
    {
        return $this->provider->getHalfOpenAttempts($this->config->prefix, $name);
    }

    #[\Override]
    public function run(string $name, callable $action, ?callable $fallback = null): mixed
    {
        $attempt = 0;

        do {
            try {
                return match ($this->provider->getState($this->config->prefix, $name)) {
                    CircuitBreakerState::OPEN => $this->handleOpenState($name, $action),
                    CircuitBreakerState::HALF_OPEN => $this->handleHalfOpenState($name, $action),
                    CircuitBreakerState::CLOSED => $this->handleClosedState($name, $action)
                };
            } catch (FailedRequestException $e) {
                // try another attempt
            } catch (UseFallbackException $e) {
                // break the loop, redirect to fallback
                break;
            } catch (ProviderException $e) {
                $this->logger?->critical('CircuitBreaker: provider exception ' . $e->getPrevious()?->getMessage());

                // break the loop, redirect to fallback
                break;
            } catch (\Throwable $e) {
                $this->logger?->critical('CircuitBreaker: unknown exception ' . $e->getMessage());

                // break the loop, redirect to fallback
                break;
            }

            usleep($this->config->retryInterval);
        } while (++$attempt < $this->config->retries);

        if ($fallback === null) {
            if ($this->config->fallbackOrNull) {
                return null;
            }

            throw new UnableToProcessException(previous: $e);
        }

        // if fallback returned empty response it means e.g. the response wasn't cached or cache was expired
        if (!$response = $fallback()) {
            throw new UnableToProcessException(previous: $e);
        }

        return $response;
    }

    private function handleClosedState(string $name, callable $action): mixed
    {
        try {
            return $this->processAction($action);
        } catch (FailedRequestException $e) {
            $this->provider->incrementFailedAttempts($this->config->prefix, $name);

            if ($this->provider->getFailedAttempts($this->config->prefix, $name) > $this->config->closedThreshold) {
                $this->provider->setState($this->config->prefix, $name, CircuitBreakerState::OPEN);

                $this->logger?->info('CircuitBreaker: state changed to OPEN');
            }

            throw $e;
        }
    }

    private function handleHalfOpenState(string $name, callable $action): mixed
    {
        try {
            $response = $this->processAction($action);

            $this->provider->incrementHalfOpenAttempts($this->config->prefix, $name);
            if ($this->provider->getHalfOpenAttempts($this->config->prefix, $name) > $this->config->halfOpenThreshold) {
                $this->provider->setState($this->config->prefix, $name, CircuitBreakerState::CLOSED);

                $this->logger?->info('CircuitBreaker: state changed to CLOSED');
            }

            return $response;
        } catch (FailedRequestException $e) {
            $this->provider->setState($this->config->prefix, $name, CircuitBreakerState::OPEN);

            $this->logger?->info('CircuitBreaker: state changed to OPEN');

            throw new UseFallbackException(previous: $e);
        }
    }

    private function handleOpenState(string $name, callable $action): mixed
    {
        if ($this->provider->getStateTimestamp($this->config->prefix, $name) + $this->config->openTimeout < time()) {
            $this->provider->setState($this->config->prefix, $name, CircuitBreakerState::HALF_OPEN);

            $this->logger?->info('CircuitBreaker: state changed to HALF_OPEN');

            return $this->handleHalfOpenState($name, $action);
        }

        throw new UseFallbackException();
    }

    private function processAction(callable $action): mixed
    {
        try {
            return $action();
        } catch (\Exception $e) {
            $this->logger?->error('CircuitBreaker: ' . $e->getMessage());

            throw new FailedRequestException(previous: $e);
        }
    }
}
