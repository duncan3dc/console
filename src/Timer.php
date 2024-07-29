<?php

namespace duncan3dc\Console;

class Timer
{
    private float $start;


    public function __construct()
    {
        $this->start = microtime(true);
    }


    public function getDuration(): Duration
    {
        $time = microtime(true) - $this->start;

        return new Duration($time);
    }
}
