<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Timer;
use duncan3dc\Console\Duration;
use PHPUnit\Framework\TestCase;

class TimerTest extends TestCase
{
    public function testGetDuration(): void
    {
        $timer = new Timer();

        $duration = $timer->getDuration();

        $this->assertInstanceOf(Duration::class, $duration);
    }
}
