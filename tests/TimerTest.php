<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Timer;
use duncan3dc\Console\Duration;

class TimerTest extends \PHPUnit_Framework_TestCase
{

    public function testGetDuration()
    {
        $timer = new Timer;

        $duration = $timer->getDuration();

        $this->assertInstanceOf(Duration::class, $duration);
    }
}
