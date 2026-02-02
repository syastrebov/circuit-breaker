<?php

namespace CircuitBreaker\Providers;

use CircuitBreaker\Enums\CircuitBreakerState;
use CircuitBreaker\Exceptions\ProviderException;

readonly class DatabaseProvider implements ProviderInterface
{
    public function __construct(
        private \PDO $pdo,
        private string $table
    ) {
    }

    public function getState(string $prefix, string $name): CircuitBreakerState
    {
        if ($state = $this->getValue($prefix, self::KEY_STATE, $name)) {
            return CircuitBreakerState::from($state);
        }

        return CircuitBreakerState::CLOSED;
    }

    public function getStateTimestamp(string $prefix, string $name): int
    {
        return (int) $this->getValue($prefix, self::KEY_STATE_TIMESTAMP, $name);
    }

    public function getFailedAttempts(string $prefix, string $name): int
    {
        return (int) $this->getValue($prefix, self::KEY_FAILED_ATTEMPTS, $name);
    }

    public function getHalfOpenAttempts(string $prefix, string $name): int
    {
        return (int) $this->getValue($prefix, self::KEY_HALF_OPEN_ATTEMPTS, $name);
    }

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

    public function incrementFailedAttempts(string $prefix, string $name): void
    {
        $this->increment($prefix, self::KEY_FAILED_ATTEMPTS, $name);
    }

    public function incrementHalfOpenAttempts(string $prefix, string $name): void
    {
        $this->increment($prefix, self::KEY_HALF_OPEN_ATTEMPTS, $name);
    }

    private function getValue(string $prefix, string $column, string $name): ?string
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT $column 
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

    private function increment(string $prefix, string $column, string $name): void
    {
        try {
            $query = match ($this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
                'mysql' => "
                    INSERT INTO $this->table (prefix, name, $column) VALUES (:prefix, :name, 1) 
                    ON DUPLICATE KEY UPDATE $column = $column + 1
                ",
                'pgsql', 'sqlite' => "
                    INSERT INTO $this->table (prefix, name, $column) VALUES (:prefix, :name, 1) 
                    ON CONFLICT (prefix, name) DO UPDATE SET $column = $this->table.$column + 1
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
