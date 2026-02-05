<?php

namespace Tests\Unit\CircuitBreaker\Providers;

use CircuitBreaker\Providers\MemoryProvider;

class MemoryProviderTest extends ProviderTestCase
{
    public function setUp(): void
    {
        $this->provider = new MemoryProvider();
    }
}
