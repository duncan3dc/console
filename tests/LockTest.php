<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Application;
use duncan3dc\Console\Command;
use duncan3dc\SymfonyCLImate\Output;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

use function exec;

class LockTest extends TestCase
{
    /** @var Application */
    private $application;

    /** @var Output&MockInterface */
    private $output;


    public function setUp(): void
    {
        $this->application = new Application();
        $this->output = Mockery::mock(Output::class);
    }


    public function tearDown(): void
    {
        Mockery::close();
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
        $this->application->loadCommands(__DIR__ . "/commands/base");

        $command = $this->application->get("category:do-stuff");
        $this->assertInstanceOf(Command::class, $command);

        $command->lock($this->output);

        $output = [];
        exec(__DIR__ . "/app.php category:do-stuff --hide-resource-info", $output, $result);
        $this->assertSame(Application::STATUS_LOCKED, $result);
        $this->assertSame(["Another instance of this command (category:do-stuff) is currently running"], $output);

        $command->unlock();

        $output = [];
        exec(__DIR__ . "/app.php category:do-stuff --hide-resource-info", $output, $result);
        $this->assertSame(0, $result);
        $this->assertSame([], $output);
    }


    public function testDoNotLock()
    {
        $this->application->loadCommands(__DIR__ . "/commands/base");

        $command = $this->application->get("category:no-lock");
        $this->assertInstanceOf(Command::class, $command);
        $command->lock($this->output);

        $output = [];
        exec(__DIR__ . "/app.php category:no-lock --hide-resource-info", $output, $result);
        $this->assertSame(0, $result);
        $this->assertSame([], $output);

        $command->unlock();
    }
}
