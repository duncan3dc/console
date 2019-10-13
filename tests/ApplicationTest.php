<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Application;
use duncan3dc\Mock\CoreFunction;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

use function exec;
use function is_dir;
use function runApplication;

class ApplicationTest extends TestCase
{
    /** @var Application */
    private $application;


    public function setUp(): void
    {
        $this->application = new Application();
    }


    /**
     * @inheritdoc
     */
    public function tearDown(): void
    {
        $fs = new Filesystem();

        if ($fs->exists("/tmp/locks")) {
            $fs->remove("/tmp/locks");
        }

        if ($fs->exists("/tmp/console-locks")) {
            $fs->remove("/tmp/console-locks");
        }

        CoreFunction::close();
    }


    public function testLoadCommands()
    {
        $this->application->loadCommands(__DIR__ . "/commands/base");

        $this->assertTrue($this->application->has("category:do-stuff"));
    }


    public function testLoadCommandsNamespace()
    {
        $this->application->loadCommands(__DIR__ . "/commands/extra", "Extra");

        $this->assertTrue($this->application->has("category:do-stuff"));
    }


    public function testLoadCommandsInvalidDirectory()
    {
        $path = __DIR__ . "/no-such-directory";
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The \"{$path}\" directory does not exist.");
        $this->application->loadCommands($path);
    }


    public function testLoadCommandsEmptyDirectory()
    {
        $path = __DIR__ . "/commands/empty-directory";
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("No commands were found in the path ({$path})");
        $this->application->loadCommands($path);
    }


    /**
     * Ensure we don't try to instantiate interfaces or abstract classes.
     */
    public function testLoadCommands5(): void
    {
        $this->application->loadCommands(__DIR__ . "/commands/instantiate", "Instantiate");

        $this->assertTrue($this->application->has("concretes:do-stuff"));
    }


    /**
     * Allow a custom suffix to be used for the commands.
     */
    public function testLoadCommands6(): void
    {
        $this->application->loadCommands(__DIR__ . "/commands/tasks", "Tasks", "Task");

        $this->assertTrue($this->application->has("category:do-stuff"));
        $this->assertFalse($this->application->has("category:ignore-me"));
    }


    public function testSetLockPath1(): void
    {
        $path = "/tmp/does_not_exist";
        $fs = new Filesystem();
        if ($fs->exists($path)) {
            $fs->remove($path);
        }

        $this->application->setLockPath($path);

        $this->assertTrue($fs->exists($path));
    }


    public function testSetLockPath2(): void
    {
        CoreFunction::mock("is_dir")->once()->with("/invalid/path/name")->andReturn(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The directory (/invalid/path/name) is unavailable");
        $this->application->setLockPath("/invalid/path/name");
    }


    public function testGetLockFactory1(): void
    {
        $this->application->getLockFactory();

        $this->assertTrue(is_dir("/tmp/console-locks"));
    }


    public function testCommandLineExitCode()
    {
        $result = runApplication("exception");

        $this->assertGreaterThan(0, $result->exit);
    }
}
