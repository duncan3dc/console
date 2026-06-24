<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Timer;
use duncan3dc\Console\Duration;
use PHPUnit\Framework\TestCase;

class TimerTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testGetDuration(): void
    {
        $timer = new Timer();
        $timer->getDuration();
    }
}
