<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Application;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TimeLimitTest extends TestCase
{
    protected $application;

    public function setUp(): void
    {
        $this->application = new Application;
    }


    public function tearDown(): void
    {
        Mockery::close();
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
