<?php

namespace duncan3dc\Console\Tests;

use duncan3dc\Console\Application;

class CommandTest extends \PHPUnit_Framework_TestCase
{

    public function testTimeLimit()
    {
        $application = new Application;

        $input = $this->getMock("Symfony\Component\Console\Input\InputInterface");
        $output = $this->getMock("Symfony\Component\Console\Output\OutputInterface");

        $application->loadCommands(__DIR__ . "/commands/base");

        $start = time();
        $application->runCommand("category:time-limit", [], $input, $output);
        $runtime = time() - $start;
        $this->assertGreaterThan(1, $runtime);
        $this->assertLessThan(4, $runtime);
    }


    public function testNoTimeLimit()
    {
        $application = new Application;

        $reflection = new \ReflectionClass($application);
        $timeLimit = $reflection->getProperty("timeLimit");
        $timeLimit->setAccessible(true);
        $timeLimit->setValue($application, false);

        $input = $this->getMock("Symfony\Component\Console\Input\InputInterface");
        $output = $this->getMock("Symfony\Component\Console\Output\OutputInterface");

        $application->loadCommands(__DIR__ . "/commands/base");

        $start = time();
        $application->runCommand("category:time-limit", [], $input, $output);
        $runtime = time() - $start;
        $this->assertGreaterThan(4, $runtime);
    }
}
