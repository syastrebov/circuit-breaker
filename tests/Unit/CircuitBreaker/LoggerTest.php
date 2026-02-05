<?php

namespace Tests\Unit\CircuitBreaker;

use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\CircuitBreakerConfig;
use CircuitBreaker\Enums\CircuitBreakerState;
use CircuitBreaker\Providers\MemoryProvider;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;

class LoggerTest extends \PHPUnit\Framework\TestCase
{
    public function testFailedRequestLogs(): void
    {
        $handler = new TestHandler();

        $circuit = new CircuitBreaker(
            provider: new MemoryProvider(),
            config: new CircuitBreakerConfig(
                retries: 2,
                closedThreshold: 6,
                fallbackOrNull: true
            ),
            logger: new Logger('my_logger', [$handler])
        );

        $circuit->run('test', function (): void {
            throw new \RuntimeException('unable to fetch data');
        });

        $this->assertCount(2, $handler->getRecords());
        $this->assertEquals('CircuitBreaker: unable to fetch data', $handler->getRecords()[0]->message);
        $this->assertEquals(Level::Error, $handler->getRecords()[0]->level);
        $this->assertEquals('CircuitBreaker: unable to fetch data', $handler->getRecords()[1]->message);
        $this->assertEquals(Level::Error, $handler->getRecords()[1]->level);
    }

    public function testChangeToOpenStateLog(): void
    {
        $handler = new TestHandler();

        $circuit = new CircuitBreaker(
            provider: new MemoryProvider(),
            config: new CircuitBreakerConfig(
                retries: 2,
                closedThreshold: 1,
                fallbackOrNull: true
            ),
            logger: new Logger('my_logger', [$handler])
        );

        $circuit->run('test', function (): void {
            throw new \RuntimeException('unable to fetch data');
        });

        $this->assertCount(3, $handler->getRecords());
        $this->assertEquals('CircuitBreaker: unable to fetch data', $handler->getRecords()[0]->message);
        $this->assertEquals(Level::Error, $handler->getRecords()[0]->level);
        $this->assertEquals('CircuitBreaker: unable to fetch data', $handler->getRecords()[1]->message);
        $this->assertEquals(Level::Error, $handler->getRecords()[1]->level);
        $this->assertEquals('CircuitBreaker: state changed to OPEN', $handler->getRecords()[2]->message);
        $this->assertEquals(Level::Info, $handler->getRecords()[2]->level);
    }

    public function testChangeToHalfOpenStateLog(): void
    {
        $handler = new TestHandler();
        $provider = new MemoryProvider();

        $circuit = new CircuitBreaker(
            provider: $provider,
            config: new CircuitBreakerConfig(
                retries: 2,
                halfOpenThreshold: 5,
                openTimeout: 1,
                fallbackOrNull: true
            ),
            logger: new Logger('my_logger', [$handler])
        );

        $provider->setState(CircuitBreakerConfig::DEFAULT_PREFIX, 'test', CircuitBreakerState::OPEN);

        sleep(2);

        $circuit->run('test', function (): string {
            return '{"response": "data"}';
        });

        $this->assertCount(1, $handler->getRecords());
        $this->assertEquals('CircuitBreaker: state changed to HALF_OPEN', $handler->getRecords()[0]->message);
        $this->assertEquals(Level::Info, $handler->getRecords()[0]->level);
    }

    public function testChangeStateToClosedLog(): void
    {
        $handler = new TestHandler();
        $provider = new MemoryProvider();

        $circuit = new CircuitBreaker(
            provider: $provider,
            config: new CircuitBreakerConfig(
                retries: 2,
                halfOpenThreshold: 1,
                openTimeout: 1,
                fallbackOrNull: true
            ),
            logger: new Logger('my_logger', [$handler])
        );

        $provider->setState(CircuitBreakerConfig::DEFAULT_PREFIX, 'test', CircuitBreakerState::OPEN);

        sleep(2);

        $run = fn (): string => $circuit->run('test', function (): string {
            return '{"response": "data"}';
        });

        $run();
        $run();

        $this->assertCount(2, $handler->getRecords());
        $this->assertEquals('CircuitBreaker: state changed to HALF_OPEN', $handler->getRecords()[0]->message);
        $this->assertEquals(Level::Info, $handler->getRecords()[0]->level);
        $this->assertEquals('CircuitBreaker: state changed to CLOSED', $handler->getRecords()[1]->message);
        $this->assertEquals(Level::Info, $handler->getRecords()[1]->level);
    }
}
