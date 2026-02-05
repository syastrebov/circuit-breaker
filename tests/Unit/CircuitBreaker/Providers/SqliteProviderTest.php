<?php

namespace Tests\Unit\CircuitBreaker\Providers;

use CircuitBreaker\Providers\DatabaseProvider;

final class SqliteProviderTest extends ProviderTestCase
{
    public function setUp(): void
    {
        $table = 'circuit_breaker';
        $databaseFile = __DIR__ . '/database.sqlite';

        $pdo = new \PDO("sqlite:$databaseFile");
        $pdo->prepare("    
            CREATE TABLE IF NOT EXISTS $table (
                prefix VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                state TEXT CHECK(state IN ('open', 'half_open', 'closed')),
                state_timestamp INTEGER,
                half_open_attempts INTEGER,
                failed_attempts INTEGER,
                CONSTRAINT prefix_name_unique UNIQUE (prefix, name)
            );
        ")->execute();

        $this->provider = new DatabaseProvider($pdo, $table);
    }
}
