PHP implementation of circuit breaker wrapper for microservices and api calls.

* Laravel package: https://github.com/syastrebov/laravel-circuit-breaker
* Symfony package: https://github.com/syastrebov/circuit-breaker-bundle

## Install

~~~bash
composer require syastrebov/circuit-breaker
~~~

## Usage

### Simple usage:

~~~php
use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\Providers\RedisProvider;

$redis = new \Redis();
$redis->connect('redis');

$circuit = new CircuitBreaker(new RedisProvider($redis));
$response = $circuit->run(
    $name,
    function () {
        // call your api
        return '{"response": "data"}';
    }
);

// {"response": "data"}
echo $response;
~~~

### Use fallback:

~~~php
use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\Providers\RedisProvider;

$redis = new \Redis();
$redis->connect('redis');

$circuit = new CircuitBreaker(new RedisProvider($redis));
$response = $circuit->run(
    $name,
    // action
    function () {
        throw new \RuntimeException('unable to fetch data');
    },
    // fallback
    function () {
        // call your api
        return '{"response": "cached data"}';
    }
);

// {"response": "cached data"}
echo $response;
~~~

### Use exception:

~~~php
use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\Providers\RedisProvider;
use CircuitBreaker\Exceptions;

$redis = new \Redis();
$redis->connect('redis');

$circuit = new CircuitBreaker(new RedisProvider($redis));

try {
    $response = $circuit->run(
        $name,
        // action
        function () {
            throw new \RuntimeException('unable to fetch data');
        }
    );
} catch (UnableToProcessException $e) {
    // handle exception
}
~~~

## Config

~~~php
use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\Providers\RedisProvider;
use CircuitBreaker\CircuitBreakerConfig;

$redis = new \Redis();
$redis->connect('redis');

$circuit = new CircuitBreaker(new RedisProvider($redis), new CircuitBreakerConfig(
    // Prefix
    prefix: 'api',
    // Number of attempts within run() action
    retries: 5,
    // Number of failed attempts to change state to 'OPEN'
    closedThreshold: 2,
    // Number of succeed attempts to change state to 'CLOSED'
    halfOpenThreshold: 2,
    // Delay between retries within run() action in microseconds
    retryInterval: 1000,
    // TTL of OPEN state in seconds
    openTimeout: 60,
    // If true and no fallback defined returns NULL otherwise throws UnableToProcessException 
    fallbackOrNull: true
));
~~~

## Supported Drivers

### Redis:

~~~php
use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\Providers\RedisProvider;

$redis = new \Redis();
$redis->connect('redis');

$circuit = new CircuitBreaker(new RedisProvider($redis));
~~~

### Redis cluster:

~~~php
use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\Providers\RedisProvider;

$redis = new \RedisCluster(
    'my cluster',
    [
        'redis-node-1:6379',
        'redis-node-2:6379',
        'redis-node-3:6379',
    ],
    1.5,
    1.5,
    true
);

$circuit = new CircuitBreaker(new RedisProvider($redis));
~~~

### Memcached:

~~~php
use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\Providers\MemcachedProvider;

$memcached = new \Memcached();
$memcached->addServer('memcached', 11211);

$circuit = new CircuitBreaker(new MemcachedProvider($memcached));
~~~

### MySQL:

~~~php
use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\Providers\DatabaseProvider;

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

$provider = new DatabaseProvider($pdo, $table);
~~~

### PostgreSQL:

~~~php
use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\Providers\DatabaseProvider;

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

$provider = new DatabaseProvider($pdo, $table);
~~~

### SQLite:

~~~php
use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\Providers\DatabaseProvider;

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

$provider = new DatabaseProvider($pdo, $table);
~~~

## Run tests

~~~bash
docker compose up -d
docker exec -t circuit-breaker-php vendor/bin/phpunit
~~~
