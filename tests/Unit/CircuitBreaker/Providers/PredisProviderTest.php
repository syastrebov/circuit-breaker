<?php

namespace Tests\Unit\CircuitBreaker\Providers;

use CircuitBreaker\Providers\PredisProvider;
use Predis\Client;

final class PredisProviderTest extends ProviderTestCase
{
    public function setUp(): void
    {
        $predis = new Client([
            'host' => 'redis',
        ]);

        $predis->connect();

        $this->provider = new PredisProvider($predis);
    }
}
