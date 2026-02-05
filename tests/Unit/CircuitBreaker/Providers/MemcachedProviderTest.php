<?php

namespace Tests\Unit\CircuitBreaker\Providers;

use CircuitBreaker\Providers\MemcachedProvider;

final class MemcachedProviderTest extends ProviderTestCase
{
    public function setUp(): void
    {
        $memcached = new \Memcached();
        $memcached->addServer('memcached', 11211);

        $this->provider = new MemcachedProvider($memcached);
    }
}
