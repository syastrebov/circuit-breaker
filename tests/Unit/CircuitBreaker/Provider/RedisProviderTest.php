<?php

namespace Tests\Unit\CircuitBreaker\Provider;

use CircuitBreaker\Provider\RedisProvider;

class RedisProviderTest extends ProviderTestCase
{
    public function setUp(): void
    {
        $redis = new \Redis();
        $redis->connect('redis');

        $this->provider = new RedisProvider($redis);
    }
}
