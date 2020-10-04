<?php

namespace duncan3dc\ConsoleTests;

use duncan3dc\Console\Duration;
use PHPUnit\Framework\TestCase;

class DurationTest extends TestCase
{

    /**
     * @return array<float[]|string[]>
     */
    public function formatProvider(): iterable
    {
        $data = [
            "0.1"   =>  "100 ms",
            "1"     =>  "1 seconds",
            "2"     =>  "2 seconds",
            "59"    =>  "59 seconds",
            "60"    =>  "1 minutes",
            "90"    =>  "1.5 minutes",
            "119"   =>  "1.98 minutes",
            "120"   =>  "2 minutes",
            "3540"  =>  "59 minutes",
            "3600"  =>  "1 hours",
        ];
        foreach ($data as $time => $expected) {
            yield [(float) $time, $expected];
        }
    }
    /**
     * @dataProvider formatProvider
     */
    public function testFormat(float $time, string $expected): void
    {
        $duration = new Duration($time);

        $result = $duration->format();

        $this->assertSame($expected, $result);
    }
}
