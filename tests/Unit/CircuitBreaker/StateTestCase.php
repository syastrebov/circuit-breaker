<?php

namespace Tests\Unit\CircuitBreaker;

use CircuitBreaker\Contracts\ProviderInterface;
use CircuitBreaker\Providers\MemoryProvider;
use Tests\Unit\CircuitBreaker\Traits\DefaultConfigTrait;

abstract class StateTestCase extends \PHPUnit\Framework\TestCase
{
    use DefaultConfigTrait;

    protected ProviderInterface $provider;

    public function setUp(): void
    {
        $this->provider = new MemoryProvider();
    }
}
