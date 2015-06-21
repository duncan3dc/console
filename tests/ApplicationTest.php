<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Application;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{

    public function testLoadCommands()
    {
        $application = new Application;

        $application->loadCommands(__DIR__ . "/commands/base");

        $this->assertTrue($application->has("category:do-stuff"));
    }


    public function testLoadCommandsNamespace()
    {
        $application = new Application;

        $application->loadCommands(__DIR__ . "/commands/extra", "Extra");

        $this->assertTrue($application->has("category:do-stuff"));
    }


    public function testLoadCommandsInvalidDirectory()
    {
        $path = __DIR__ . "/no-such-directory";
        $this->setExpectedException("InvalidArgumentException", 'The "' . $path . '" directory does not exist.');
        (new Application)->loadCommands($path);
    }


    public function testLoadCommandsEmptyDirectory()
    {
        $path = __DIR__ . "/commands/empty-directory";
        $this->setExpectedException("InvalidArgumentException", "No commands were found in the path (" . $path . ")");
        (new Application)->loadCommands($path);
    }
}
