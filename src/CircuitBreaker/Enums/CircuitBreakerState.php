<?php

namespace CircuitBreaker\Enums;

enum CircuitBreakerState: string
{
    case CLOSED = 'closed';
    case HALF_OPEN = 'half_open';
    case OPEN = 'open';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
