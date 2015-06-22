<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;

class ListTest extends \PHPUnit_Framework_TestCase
{
    public function testOutput()
    {
        $application = new Application;

        $_SERVER["argv"][1] = "category:";

        $output = $this->getMock(OutputInterface::class);

        $application->loadCommands(__DIR__ . "/commands/base");

        $application->setAutoExit(false);
        $application->run(null, $output);

        $this->assertSame("list", $_SERVER["argv"][1]);
        $this->assertSame("category", $_SERVER["argv"][2]);
    }
}
