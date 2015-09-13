<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Application;
use Mockery;
use Symfony\Component\Console\Output\OutputInterface;

class LockTest extends \PHPUnit_Framework_TestCase
{
    protected $application;

    public function setUp()
    {
        $this->application = new Application;
    }


    public function testLockPath()
    {
        $this->application->loadCommands(__DIR__ . "/commands/base");

        $command = $this->application->get("category:do-stuff");
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
        $output = Mockery::mock(OutputInterface::class);

        $this->application->loadCommands(__DIR__ . "/commands/base");

        $command = $this->application->get("category:do-stuff");
        $command->lock($output);

        $lock = fopen($command->getLockPath(), "w");
        $this->assertFalse(flock($lock, LOCK_EX | LOCK_NB));
        fclose($lock);

        $command->unlock($output);

        $lock = fopen($command->getLockPath(), "w");
        $this->assertTrue(flock($lock, LOCK_EX | LOCK_NB));
        flock($lock, LOCK_UN);
        fclose($lock);
    }


    public function testDoNotLock()
    {
        $output = Mockery::mock(OutputInterface::class);

        $this->application->loadCommands(__DIR__ . "/commands/base");

        $command = $this->application->get("category:no-lock");
        $command->lock($output);

        $lock = fopen($command->getLockPath(), "w");
        $this->assertTrue(flock($lock, LOCK_EX | LOCK_NB));
        flock($lock, LOCK_UN);
        fclose($lock);

        $command->unlock($output);
    }
}
