<?php

namespace Tests\Unit\CircuitBreaker\Providers;

use CircuitBreaker\Providers\PredisProvider;
use Predis\Client;

final class PredisClusterProviderTest extends ProviderTestCase
{
    public function setUp(): void
    {
        $nodes = [
            'redis-node-1:6379',
            'redis-node-2:6379',
            'redis-node-3:6379',
        ];

        $options = [
            // 'redis' (server-side) or 'predis' (client-side)
            'cluster' => 'redis',
        ];

        $predis = new Client($nodes, $options);

        $predis->connect();

        $this->provider = new PredisProvider($predis);
    }
}
