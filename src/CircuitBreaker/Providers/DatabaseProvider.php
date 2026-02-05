<?php

namespace CircuitBreaker\Providers;

use CircuitBreaker\Enums\CircuitBreakerState;
use CircuitBreaker\Exceptions\ProviderException;

final class DatabaseProvider extends AbstractProvider
{
    public function __construct(
        private readonly \PDO $pdo,
        private readonly string $table
    ) {
    }

    #[\Override]
    public function setState(string $prefix, string $name, CircuitBreakerState $state): void
    {
        try {
            $query = match ($this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
                'mysql' => "
                    INSERT INTO $this->table (
                       prefix,
                       name,
                       " . self::KEY_STATE . ", 
                       " . self::KEY_FAILED_ATTEMPTS . ", 
                       " . self::KEY_HALF_OPEN_ATTEMPTS . ", 
                       " . self::KEY_STATE_TIMESTAMP . "
                    ) VALUES (:prefix, :name, :state, 0, 0, :state_timestamp) 
                    ON DUPLICATE KEY UPDATE 
                        " . self::KEY_STATE . " = :state,
                        " . self::KEY_FAILED_ATTEMPTS . " = 0,
                        " . self::KEY_HALF_OPEN_ATTEMPTS . " = 0,
                        " . self::KEY_STATE_TIMESTAMP . " = :state_timestamp
                ",
                'pgsql', 'sqlite' => "
                    INSERT INTO $this->table (
                       prefix,
                       name,
                       " . self::KEY_STATE . ", 
                       " . self::KEY_FAILED_ATTEMPTS . ", 
                       " . self::KEY_HALF_OPEN_ATTEMPTS . ", 
                       " . self::KEY_STATE_TIMESTAMP . "
                    ) VALUES (:prefix, :name, :state, 0, 0, :state_timestamp) 
                    ON CONFLICT (prefix, name) DO UPDATE SET
                        " . self::KEY_STATE . " = :state,
                        " . self::KEY_FAILED_ATTEMPTS . " = 0,
                        " . self::KEY_HALF_OPEN_ATTEMPTS . " = 0,
                        " . self::KEY_STATE_TIMESTAMP . " = :state_timestamp
                ",
                default => throw new ProviderException('Unsupported database driver')
            };

            $stmt = $this->pdo->prepare($query);
            $stmt->execute([
                ':prefix' => $prefix,
                ':name' => $name,
                'state' => $state->value,
                ':state_timestamp' => time(),
            ]);
        } catch (\PDOException $e) {
            throw new ProviderException(previous: $e);
        }
    }

    #[\Override]
    protected function getValue(string $prefix, string $name, string $type): string|int|null
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT $type 
                FROM $this->table 
                WHERE prefix = :prefix AND name = :name
            ");

            $stmt->execute([
                ':prefix' => $prefix,
                ':name' => $name,
            ]);

            return $stmt->fetchColumn();
        } catch (\PDOException $e) {
            throw new ProviderException(previous: $e);
        }
    }

    #[\Override]
    protected function increment(string $prefix, string $name, string $type): void
    {
        try {
            $query = match ($this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
                'mysql' => "
                    INSERT INTO $this->table (prefix, name, $type) VALUES (:prefix, :name, 1) 
                    ON DUPLICATE KEY UPDATE $type = $type + 1
                ",
                'pgsql', 'sqlite' => "
                    INSERT INTO $this->table (prefix, name, $type) VALUES (:prefix, :name, 1) 
                    ON CONFLICT (prefix, name) DO UPDATE SET $type = $this->table.$type + 1
                ",
                default => throw new ProviderException('Unsupported database driver')
            };

            $stmt = $this->pdo->prepare($query);
            $stmt->execute([
                ':prefix' => $prefix,
                ':name' => $name,
            ]);
        } catch (\PDOException $e) {
            throw new ProviderException(previous: $e);
        }
    }
}
