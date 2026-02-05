<?php

namespace Tests\Unit\CircuitBreaker\Providers;

use CircuitBreaker\Providers\RedisProvider;

final class RedisClusterProviderTest extends ProviderTestCase
{
    public function setUp(): void
    {
        $this->provider = new RedisProvider(
            new \RedisCluster(
                'my cluster',
                [
                    'redis-node-1:6379',
                    'redis-node-2:6379',
                    'redis-node-3:6379',
                ],
                1.5,
                1.5,
                true
            )
        );
    }
}
