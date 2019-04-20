<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Application;
use duncan3dc\SymfonyCLImate\Output;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Mockery\MockInterface;

class TimeLimitTest extends TestCase
{
    /** @var Application */
    private $application;

    /** @var InputInterface&MockInterface */
    private $input;

    /** @var OutputInterface&MockInterface */
    private $output;


    public function setUp(): void
    {
        $this->application = new Application();

        $this->input = Mockery::mock(InputInterface::class);
        $this->output = Mockery::mock(Output::class);

        $this->input->shouldIgnoreMissing();
        $this->output->shouldIgnoreMissing();
    }


    public function tearDown(): void
    {
        Mockery::close();
    }


    public function testTimeLimit()
    {
        $this->application->loadCommands(__DIR__ . "/commands/base");

        $start = time();
        $this->application->runCommand("category:time-limit", [], $this->input, $this->output);
        $runtime = time() - $start;
        $this->assertGreaterThan(1, $runtime);
        $this->assertLessThan(4, $runtime);
    }


    public function testNoTimeLimit()
    {
        $this->application->setAutoExit(false);
        $this->application->loadCommands(__DIR__ . "/commands/base");

        $input = new ArrayInput(["category:time-limit", "--no-time-limit" => true]);

        $start = time();
        $this->application->run($input, $this->output);
        $runtime = time() - $start;
        $this->assertGreaterThan(4, $runtime);
    }
}
