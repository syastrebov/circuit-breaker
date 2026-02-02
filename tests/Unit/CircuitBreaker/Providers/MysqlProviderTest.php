<?php

namespace Tests\Unit\CircuitBreaker\Providers;

use CircuitBreaker\Providers\DatabaseProvider;

class MysqlProviderTest extends ProviderTestCase
{
    public function setUp(): void
    {
        $table = 'circuit_breaker';

        $pdo = new \PDO("mysql:host=mysql;dbname=database", 'user', 'password');
        $pdo->prepare("
            CREATE TABLE IF NOT EXISTS $table (
                prefix VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                state ENUM('closed', 'open', 'half_open'),
                state_timestamp INT,
                half_open_attempts INT,
                failed_attempts INT,
                CONSTRAINT prefix_name_unique UNIQUE (prefix, name)
            );
        ")->execute();

        $this->provider = new DatabaseProvider($pdo, $table);
    }
}
