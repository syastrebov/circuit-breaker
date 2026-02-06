<?php

namespace CircuitBreaker\Enums;

enum Provider: string
{
    case Redis = 'redis';
    case Predis = 'predis';
    case Memcached = 'memcached';
    case Database = 'database';
    case Memory = 'memory';
}
