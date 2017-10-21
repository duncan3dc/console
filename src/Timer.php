<?php

namespace duncan3dc\Console;

class Timer
{
    /**
     * @var float $start The start time of the timer
     */
    private $start;


    /**
     * Create a new timer.
     */
    public function __construct()
    {
        $this->start = microtime(true);
    }


    /**
     * Get the length of time the timer has been running.
     *
     * @return Duration
     */
    public function getDuration()
    {
        $time = microtime(true) - $this->start;

        return new Duration($time);
    }
}
