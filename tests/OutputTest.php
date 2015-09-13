<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Output;
use Mockery;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class OutputTest extends \PHPUnit_Framework_TestCase
{
    protected $output;
    protected $consoleOutput;

    public function setUp()
    {
        $this->consoleOutput = Mockery::mock(ConsoleOutput::class);
        $this->output = new Output;

        $reflected = new \ReflectionClass($this->output);
        $property = $reflected->getProperty("console");
        $property->setAccessible(true);
        $property->setValue($this->output, $this->consoleOutput);
    }


    public function tearDown()
    {
        Mockery::close();
    }


    public function testWrite1()
    {
        $this->consoleOutput->shouldReceive("write")->once()->with("ok", false, OutputInterface::OUTPUT_NORMAL);
        $this->output->write("ok");
    }
    public function testWrite2()
    {
        $this->consoleOutput->shouldReceive("write")->once()->with("ok", true, OutputInterface::OUTPUT_NORMAL);
        $this->output->write("ok", true);
    }
    public function testWrite3()
    {
        $this->consoleOutput->shouldReceive("write")->once()->with("ok", true, OutputInterface::OUTPUT_PLAIN);
        $this->output->write("ok", true, OutputInterface::OUTPUT_PLAIN);
    }


    public function testWriteln1()
    {
        $this->consoleOutput->shouldReceive("writeln")->once()->with("ok", OutputInterface::OUTPUT_NORMAL);
        $this->output->writeln("ok");
    }
    public function testWriteln2()
    {
        $this->consoleOutput->shouldReceive("writeln")->once()->with("ok", OutputInterface::OUTPUT_PLAIN);
        $this->output->writeln("ok", OutputInterface::OUTPUT_PLAIN);
    }


    public function testSetVerbosity()
    {
        $this->consoleOutput->shouldReceive("setVerbosity")->once()->with(7);
        $this->output->setVerbosity(7);
    }


    public function testGetVerbosity()
    {
        $this->consoleOutput->shouldReceive("getVerbosity")->once()->with()->andReturn(8);
        $result = $this->output->getVerbosity();
        $this->assertSame(8, $result);
    }


    public function testSetFormatter()
    {
        $formatter = Mockery::mock(OutputFormatterInterface::class);

        $this->consoleOutput->shouldReceive("setFormatter")->once()->with($formatter);
        $this->output->setFormatter($formatter);
    }


    public function testGetFormatter()
    {
        $this->consoleOutput->shouldReceive("getFormatter")->once()->with()->andReturn("formatter");
        $result = $this->output->getFormatter();
        $this->assertSame("formatter", $result);
    }


    public function testIsQuiet()
    {
        $this->consoleOutput->shouldReceive("isQuiet")->once()->with()->andReturn("quiet");
        $result = $this->output->isQuiet();
        $this->assertSame("quiet", $result);
    }


    public function testIsVerbose()
    {
        $this->consoleOutput->shouldReceive("isVerbose")->once()->with()->andReturn("verbose");
        $result = $this->output->isVerbose();
        $this->assertSame("verbose", $result);
    }


    public function testIsVeryVerbose()
    {
        $this->consoleOutput->shouldReceive("isVeryVerbose")->once()->with()->andReturn("very-verbose");
        $result = $this->output->isVeryVerbose();
        $this->assertSame("very-verbose", $result);
    }


    public function testIsDebug()
    {
        $this->consoleOutput->shouldReceive("isDebug")->once()->with()->andReturn("debug");
        $result = $this->output->isDebug();
        $this->assertSame("debug", $result);
    }


    public function testSetDecorated()
    {
        $this->consoleOutput->shouldReceive("setDecorated")->once()->with(7);
        $this->output->setDecorated(7);
    }


    public function testIsDecorated()
    {
        $this->consoleOutput->shouldReceive("isDecorated")->once()->with()->andReturn("decorated");
        $result = $this->output->isDecorated();
        $this->assertSame("decorated", $result);
    }
}
