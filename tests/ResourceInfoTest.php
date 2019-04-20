<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Application;
use League\CLImate\TerminalObject\Basic\Out;
use Mockery;
use PHPUnit\Framework\TestCase;
use function preg_match;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Mockery\MockInterface;

class ResourceInfoTest extends TestCase
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

        $this->input = new ArrayInput(["category:output-stuff"]);
        $this->output = new Output();
    }


    public function tearDown(): void
    {
        Mockery::close();
    }


    /**
     * Ensure resource info is shown by default.
     */
    public function testResourceInfo1(): void
    {
        $this->application->run($this->input, $this->output);

        $regex = "/^Some content\n\[category:output-stuff] Time: [0-9]+ ms, Memory: [0-9\.]+mb\n$/";
        $output = $this->output->getContent();
        $this->assertSame(1, preg_match($regex, $output), $output);
    }


    /**
     * Ensure resource info is hidden in quiet mode.
     */
    public function testResourceInfo2(): void
    {
        $input = new ArrayInput([$this->input->getFirstArgument(), "--quiet" => true]);

        $this->application->run($input, $this->output);

        $output = $this->output->getContent();
        $this->assertSame("Some content\n", $output);
    }


    /**
     * Ensure resource info is hidden by the --hide-resource-info option.
     */
    public function testResourceInfo3(): void
    {
        $input = new ArrayInput([$this->input->getFirstArgument(), "--hide-resource-info" => true]);

        $this->application->run($input, $this->output);

        $output = $this->output->getContent();
        $this->assertSame("Some content\n", $output);
    }


    /**
     * Ensure that --show-resource-info takes precedence.
     */
    public function testResourceInfo4(): void
    {
        $input = new ArrayInput([$this->input->getFirstArgument(), "--show-resource-info" => true]);

        $this->application->run($input, $this->output);

        $regex = "/^Some content\n\[category:output-stuff] Time: [0-9]+ ms, Memory: [0-9\.]+mb\n$/";
        $output = $this->output->getContent();
        $this->assertSame(1, preg_match($regex, $output), $output);
    }
}
