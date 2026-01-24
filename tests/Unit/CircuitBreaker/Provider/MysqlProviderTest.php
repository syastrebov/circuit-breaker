<?php

namespace Tests\Unit\CircuitBreaker\Provider;

use CircuitBreaker\Provider\DatabaseProvider;

class MysqlProviderTest extends ProviderTestCase
{
    public function setUp(): void
    {
        $table = 'circuit_breaker';

        $pdo = new \PDO("mysql:host=mysql;dbname=database", 'user', 'password');
        $pdo->prepare("
            CREATE TABLE IF NOT EXISTS $table (
                name VARCHAR(255) NOT NULL UNIQUE,
                state ENUM('closed', 'open', 'half_open'),
                state_timestamp INT,
                half_open_attempts INT,
                failed_attempts INT
            );
        ")->execute();

        $this->provider = new DatabaseProvider($pdo, $table);
    }
}
