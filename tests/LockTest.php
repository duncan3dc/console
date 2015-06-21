<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Application;

class LockTest extends \PHPUnit_Framework_TestCase
{

    public function testLockPath()
    {
        $application = new Application;

        $application->loadCommands(__DIR__ . "/commands/base");

        $command = $application->get("category:do-stuff");
        $this->assertSame("/tmp/console-locks/category_do-stuff.lock", $command->getLockPath());
    }


    public function testSetLockPath()
    {
        $application = new Application;

        $path = "/tmp/phpunit-test";

        $application->setLockPath($path);

        $reflection = new \ReflectionClass($application);
        $lockPath = $reflection->getProperty("lockPath");
        $lockPath->setAccessible(true);
        $this->assertSame($path, $lockPath->getValue($application));
    }


    public function testLock()
    {
        $application = new Application;

        $output = $this->getMock("Symfony\Component\Console\Output\OutputInterface");

        $application->loadCommands(__DIR__ . "/commands/base");

        $command = $application->get("category:do-stuff");
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
}
