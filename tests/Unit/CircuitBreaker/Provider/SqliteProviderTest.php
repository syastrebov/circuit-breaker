<?php

namespace Tests\Unit\CircuitBreaker\Provider;

use CircuitBreaker\Provider\DatabaseProvider;

class SqliteProviderTest extends ProviderTestCase
{
    public function setUp(): void
    {
        $table = 'circuit_breaker';
        $databaseFile = __DIR__ . '/database.sqlite';

        $pdo = new \PDO("sqlite:$databaseFile");
        $pdo->prepare("    
            CREATE TABLE IF NOT EXISTS $table (
                name VARCHAR(255) UNIQUE,
                state TEXT CHECK(state IN ('open', 'half_open', 'closed')),
                state_timestamp INTEGER,
                half_open_attempts INTEGER,
                failed_attempts INTEGER
            );
        ")->execute();

        $this->provider = new DatabaseProvider($pdo, $table);
    }
}
