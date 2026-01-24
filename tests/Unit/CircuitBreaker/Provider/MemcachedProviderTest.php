<?php

namespace Tests\Unit\CircuitBreaker\Provider;

use CircuitBreaker\Provider\MemcachedProvider;

class MemcachedProviderTest extends ProviderTestCase
{
    public function setUp(): void
    {
        $memcached = new \Memcached();
        $memcached->addServer('memcached', 11211);

        $this->provider = new MemcachedProvider($memcached);
    }
}
