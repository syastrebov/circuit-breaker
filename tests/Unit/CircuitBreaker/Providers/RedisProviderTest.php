<?php

namespace Tests\Unit\CircuitBreaker\Providers;

use CircuitBreaker\Providers\RedisProvider;

final class RedisProviderTest extends ProviderTestCase
{
    public function setUp(): void
    {
        $redis = new \Redis();
        $redis->connect('redis');

        $this->provider = new RedisProvider($redis);
    }
}
