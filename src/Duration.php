<?php

namespace duncan3dc\Console;

class Duration
{
    /**
     * @var array The segments to break the duration up into
     */
    private $times = [
        "hours"     =>  3600,
        "minutes"   =>  60,
        "seconds"   =>  1,
    ];

    /**
     * @var float The duration this instance represents.
     */
    private $time;


    /**
     * Set the start time of the timer.
     *
     * @return void
     */
    public function __construct($time)
    {
        $this->time = (float) $time;
    }


    /**
     * Formats the elapsed time as a string.
     *
     * @return string
     */
    public function format()
    {
        foreach ($this->times as $unit => $value) {
            if ($this->time >= $value) {
                $time = floor($this->time / $value * 100) / 100;
                return "{$time} {$unit}";
            }
        }

        return round($this->time * 1000) . " ms";
    }
}
