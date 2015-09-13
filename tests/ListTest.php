<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Application;
use Mockery;
use Symfony\Component\Console\Output\OutputInterface;

class ListTest extends \PHPUnit_Framework_TestCase
{
    protected $application;

    public function setUp()
    {
        $this->application = new Application;
    }

    public function testOutput()
    {
        $_SERVER["argv"][1] = "category:";

        $output = Mockery::mock(OutputInterface::class)->shouldIgnoreMissing();

        $this->application->loadCommands(__DIR__ . "/commands/base");

        $this->application->setAutoExit(false);
        $this->application->run(null, $output);

        $this->assertSame("list", $_SERVER["argv"][1]);
        $this->assertSame("category", $_SERVER["argv"][2]);
    }
}
