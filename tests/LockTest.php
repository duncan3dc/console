<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Application;
use duncan3dc\Console\Command;
use duncan3dc\SymfonyCLImate\Output;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

use function exec;
use function runApplication;

class LockTest extends TestCase
{
    /** @var Application */
    private $application;

    /** @var Output&MockInterface */
    private $output;


    protected function setUp(): void
    {
        $this->application = new Application();
        $this->output = Mockery::mock(Output::class);
    }


    protected function tearDown(): void
    {
        Mockery::close();
    }


    public function testSetLockPath(): void
    {
        $path = "/tmp/phpunit-test";

        $this->application->setLockPath($path);

        $reflection = new \ReflectionClass($this->application);
        $lockPath = $reflection->getProperty("lockPath");
        $lockPath->setAccessible(true);
        $this->assertSame($path, $lockPath->getValue($this->application));
    }


    public function testLock(): void
    {
        $this->application->loadCommands(__DIR__ . "/commands/base");

        $command = $this->application->get("category:do-stuff");
        $this->assertInstanceOf(Command::class, $command);

        $command->lock($this->output);

        $result = runApplication("category:do-stuff --hide-resource-info");
        $this->assertSame(Application::STATUS_LOCKED, $result->exit);
        $this->assertSame(["Another instance of this command (category:do-stuff) is currently running"], $result->output);

        $command->unlock();

        $result = runApplication("category:do-stuff --hide-resource-info");
        $this->assertSame(0, $result->exit);
        $this->assertSame([], $result->output);
    }


    public function testDoNotLock(): void
    {
        $this->application->loadCommands(__DIR__ . "/commands/base");

        $command = $this->application->get("category:no-lock");
        $this->assertInstanceOf(Command::class, $command);
        $command->lock($this->output);

        $result = runApplication("category:no-lock --hide-resource-info");
        $this->assertSame(0, $result->exit);
        $this->assertSame([], $result->output);

        $command->unlock();
    }
}
