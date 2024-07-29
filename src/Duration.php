<?php

namespace duncan3dc\Console;

class Duration
{
    /**
     * @var array<string, int> The segments to break the duration up into
     */
    private array $times = [
        "hours"     =>  3600,
        "minutes"   =>  60,
        "seconds"   =>  1,
    ];

    private float $time;


    public function __construct(float $time)
    {
        $this->time = $time;
    }


    /**
     * Formats the elapsed time as a string.
     */
    public function format(): string
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
