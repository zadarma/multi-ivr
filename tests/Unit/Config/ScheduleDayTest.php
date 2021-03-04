<?php

namespace MultiIvr\Tests\Unit\Config;

use MultiIvr\Config\Schedule\ScheduleDay;
use MultiIvr\Exceptions\MultiIvrException;
use PHPUnit\Framework\TestCase;

/**
 * Class ScheduleDayTest
 * @package MultiIvr\Tests\Unit\Config
 */
class ScheduleDayTest extends TestCase
{
    /**
     * @throws MultiIvrException
     */
    public function testInvalidDayNum(): void
    {
        $this->expectException(MultiIvrException::class);
        $this->expectExceptionMessage('Invalid weekday number, weekday number must be from 0 to 6.');
        new ScheduleDay(-1, [[60 * 12, 60 * 13]]);
    }

    /**
     * @throws MultiIvrException
     */
    public function testDaysInterval(): void
    {
        $this->expectException(MultiIvrException::class);
        $this->expectExceptionMessage('The minutes interval must consist of two integer values.');
        new ScheduleDay(1, [60 * 12]);
    }

    /**
     * @throws MultiIvrException
     */
    public function testDaysIntervalValues(): void
    {
        $this->expectException(MultiIvrException::class);
        $this->expectExceptionMessage('The minutes interval must consist of two integer values.');
        new ScheduleDay(1, [60 * 12, 'sss']);
    }

    /**
     * @throws MultiIvrException
     */
    public function testGetters(): void
    {
        $dayNum = 1;
        $minutesInterval = [[60 * 12, 60 * 13]];
        $day = new ScheduleDay($dayNum, $minutesInterval);
        self::assertEquals($dayNum, $day->getDayNum());
        self::assertEquals($minutesInterval, $day->getMinutesIntervals());
    }

    /**
     * @throws MultiIvrException
     */
    public function testEmptyInterval(): void
    {
        $dayNum = 1;
        $day = new ScheduleDay($dayNum);
        self::assertEquals($dayNum, $day->getDayNum());
        self::assertEquals([], $day->getMinutesIntervals());
    }
}
