<?php

namespace CircuitBreaker\Providers;

use CircuitBreaker\Enums\CircuitBreakerState;

final class MemoryProvider extends AbstractProvider
{
    private array $state = [];

    #[\Override]
    public function setState(string $prefix, string $name, CircuitBreakerState $state): void
    {
        $this->state[$prefix][$name] = [
            self::KEY_STATE => $state->value,
            self::KEY_STATE_TIMESTAMP => time(),
            self::KEY_FAILED_ATTEMPTS => 0,
            self::KEY_HALF_OPEN_ATTEMPTS => 0,
        ];
    }

    #[\Override]
    protected function getValue(string $prefix, string $name, string $type): string|int|null
    {
        return $this->state[$prefix][$name][$type] ?? null;
    }

    #[\Override]
    protected function increment(string $prefix, string $name, string $type): void
    {
        $this->state[$prefix][$name][$type] = ($this->state[$prefix][$name][$type] ?? 0) + 1;
    }
}
