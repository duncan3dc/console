<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class ApplicationTest extends TestCase
{
    protected $application;

    public function setUp()
    {
        $this->application = new Application;
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


    public function testSetLockPath()
    {
        $path = "/tmp/does_not_exist";
        $fs = new Filesystem;
        if ($fs->exists($path)) {
            $fs->remove($path);
        }

        $this->application->setLockPath($path);

        $this->assertTrue($fs->exists($path));
    }


    public function testGetLockPath()
    {
        $fs = new Filesystem;

        $path = $this->application->getLockPath();

        $this->assertTrue($fs->exists($path));
    }
}
