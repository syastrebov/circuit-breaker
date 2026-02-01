<?php

namespace CircuitBreaker\Enums;

enum CircuitBreakerState: string
{
    case CLOSED = 'closed';
    case HALF_OPEN = 'half_open';
    case OPEN = 'open';
}
