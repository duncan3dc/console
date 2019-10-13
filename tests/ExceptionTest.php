<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Application;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;

use function strpos;

class ExceptionTest extends TestCase
{
    /** @var Application */
    private $application;

    /** @var InputInterface */
    private $input;

    /** @var Output */
    private $output;


    public function setUp(): void
    {
        $this->application = new Application();
        $this->application->setAutoExit(false);

        $this->application->loadCommands(__DIR__ . "/commands/base");

        $this->input = new ArrayInput(["category:do-nothing"]);
        $this->output = new Output();
    }


    public function tearDown(): void
    {
        Mockery::close();
    }


    /**
     * Ensure the upstream exception handling is used by default.
     */
    public function testException1(): void
    {
        $this->application->run($this->input, $this->output);

        $error = "<error>  You must override the execute() method in the concrete command class.  </error>";
        $output = $this->output->getContent();
        $this->assertGreaterThan(0, strpos($output, $error), $output);
    }


    /**
     * Ensure advanced exception handling is used in very verbose mode.
     */
    public function testResourceInfo2(): void
    {
        $input = new ArrayInput([$this->input->getFirstArgument(), "-vv" => true]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("You must override the execute() method in the concrete command class");
        $this->application->run($input, $this->output);
    }
}
