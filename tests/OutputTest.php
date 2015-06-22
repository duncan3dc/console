<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Application;
use duncan3dc\Console\Output;
use Symfony\Component\Console\Input\InputInterface;

class OutputTest extends \PHPUnit_Framework_TestCase
{

    public function testOutput()
    {
        $application = new Application;

        $stdout = $this->getMock("League\CLImate\Util\Output");
        $stdout->expects($this->exactly(2))
            ->method("write")
            ->withConsecutive(
                 [$this->equalTo("\e[mSome \e[0m")],
                 [$this->equalTo("\e[mcontent\e[0m")]
             );
        $output = new Output;
        $output->setOutput($stdout);

        $input = $this->getMock(InputInterface::class);
        $input->expects($this->once())
            ->method("getFirstArgument")
            ->will($this->returnValue("category:output-stuff"));

        $application->loadCommands(__DIR__ . "/commands/base");

        $application->setAutoExit(false);
        $application->run($input, $output);
    }
}
