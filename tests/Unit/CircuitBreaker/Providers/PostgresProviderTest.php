<?php

namespace Tests\Unit\CircuitBreaker\Providers;

use CircuitBreaker\Providers\DatabaseProvider;

final class PostgresProviderTest extends ProviderTestCase
{
    public function setUp(): void
    {
        $table = 'circuit_breaker';

        $pdo = new \PDO("pgsql:host=postgres;dbname=database", 'user', 'password');
        $pdo->prepare("
            DO $$ 
            BEGIN 
                IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'state_enum') THEN 
                    CREATE TYPE state_enum AS ENUM ('closed', 'open', 'half_open'); 
                END IF; 
            END $$;
        ")->execute();
        $pdo->prepare("    
            CREATE TABLE IF NOT EXISTS $table (
                prefix VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                state state_enum NULL,
                state_timestamp INT,
                half_open_attempts INT,
                failed_attempts INT,
                CONSTRAINT prefix_name_unique UNIQUE (prefix, name)
            );
        ")->execute();

        $this->provider = new DatabaseProvider($pdo, $table);
    }
}
