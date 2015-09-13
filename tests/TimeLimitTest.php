<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Application;
use Mockery;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TimeLimitTest extends \PHPUnit_Framework_TestCase
{
    protected $application;

    public function setUp()
    {
        $this->application = new Application;
    }

    public function testTimeLimit()
    {
        $input = Mockery::mock(InputInterface::class)->shouldIgnoreMissing();
        $output = Mockery::mock(OutputInterface::class)->shouldIgnoreMissing();

        $this->application->loadCommands(__DIR__ . "/commands/base");

        $start = time();
        $this->application->runCommand("category:time-limit", [], $input, $output);
        $runtime = time() - $start;
        $this->assertGreaterThan(1, $runtime);
        $this->assertLessThan(4, $runtime);
    }


    public function testNoTimeLimit()
    {
        $reflection = new \ReflectionClass($this->application);
        $timeLimit = $reflection->getProperty("timeLimit");
        $timeLimit->setAccessible(true);
        $timeLimit->setValue($this->application, false);

        $input = Mockery::mock(InputInterface::class)->shouldIgnoreMissing();
        $output = Mockery::mock(OutputInterface::class)->shouldIgnoreMissing();

        $this->application->loadCommands(__DIR__ . "/commands/base");

        $start = time();
        $this->application->runCommand("category:time-limit", [], $input, $output);
        $runtime = time() - $start;
        $this->assertGreaterThan(4, $runtime);
    }
}
