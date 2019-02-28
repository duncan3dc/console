<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Application;
use duncan3dc\Console\Command;
use duncan3dc\SymfonyCLImate\Output;
use Mockery;
use PHPUnit\Framework\TestCase;
use function assert;
use function is_resource;

class LockTest extends TestCase
{
    /** @var Application */
    private $application;


    public function setUp(): void
    {
        $this->application = new Application();
    }


    public function tearDown(): void
    {
        Mockery::close();
    }


    public function testLockPath()
    {
        $this->application->loadCommands(__DIR__ . "/commands/base");

        $command = $this->application->get("category:do-stuff");
        $this->assertInstanceOf(Command::class, $command);
        $this->assertSame("/tmp/console-locks/category_do-stuff.lock", $command->getLockPath());
    }


    public function testSetLockPath()
    {
        $path = "/tmp/phpunit-test";

        $this->application->setLockPath($path);

        $reflection = new \ReflectionClass($this->application);
        $lockPath = $reflection->getProperty("lockPath");
        $lockPath->setAccessible(true);
        $this->assertSame($path, $lockPath->getValue($this->application));
    }


    public function testLock()
    {
        $output = Mockery::mock(Output::class);

        $this->application->loadCommands(__DIR__ . "/commands/base");

        $command = $this->application->get("category:do-stuff");
        $this->assertInstanceOf(Command::class, $command);
        $command->lock($output);

        $lock = fopen($command->getLockPath(), "w");
        assert(is_resource($lock));
        $this->assertFalse(flock($lock, LOCK_EX | LOCK_NB));
        fclose($lock);

        $command->unlock();

        $lock = fopen($command->getLockPath(), "w");
        assert(is_resource($lock));
        $this->assertTrue(flock($lock, LOCK_EX | LOCK_NB));
        flock($lock, LOCK_UN);
        fclose($lock);
    }


    public function testDoNotLock()
    {
        $output = Mockery::mock(Output::class);

        $this->application->loadCommands(__DIR__ . "/commands/base");

        $command = $this->application->get("category:no-lock");
        $this->assertInstanceOf(Command::class, $command);
        $command->lock($output);

        $lock = fopen($command->getLockPath(), "w");
        assert(is_resource($lock));
        $this->assertTrue(flock($lock, LOCK_EX | LOCK_NB));
        flock($lock, LOCK_UN);
        fclose($lock);

        $command->unlock();
    }
}
