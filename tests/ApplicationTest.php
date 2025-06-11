<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

use function runApplication;

class ApplicationTest extends TestCase
{
    /** @var Application */
    private $application;


    protected function setUp(): void
    {
        $this->application = new Application();
    }


    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $fs = new Filesystem();

        if ($fs->exists("/tmp/locks")) {
            $fs->remove("/tmp/locks");
        }

        if ($fs->exists("/tmp/console-locks")) {
            $fs->remove("/tmp/console-locks");
        }
    }


    public function testLoadCommands(): void
    {
        $this->application->loadCommands(__DIR__ . "/commands/base");

        $this->assertTrue($this->application->has("category:do-stuff"));
    }


    public function testLoadCommandsNamespace(): void
    {
        $this->application->loadCommands(__DIR__ . "/commands/extra", "Extra");

        $this->assertTrue($this->application->has("category:do-stuff"));
    }


    public function testLoadCommandsInvalidDirectory(): void
    {
        $path = __DIR__ . "/no-such-directory";
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The \"{$path}\" directory does not exist.");
        $this->application->loadCommands($path);
    }


    public function testLoadCommandsEmptyDirectory(): void
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
        $phar = new \Phar("/tmp/test.phar");
        $phar->addEmptyDir("virtual");
        $path = $phar["virtual"]->getPath();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The directory ({$path}) is unavailable");
        $this->application->setLockPath($path);
    }


    public function testGetLockFactory1(): void
    {
        $this->application->getLockFactory();

        $this->assertDirectoryExists("/tmp/console-locks");
    }


    public function testCommandLineExitCode(): void
    {
        $result = runApplication("exception");

        $this->assertGreaterThan(0, $result->exit);
    }
}
