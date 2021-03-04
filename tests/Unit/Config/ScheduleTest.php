<?php

namespace MultiIvr\Tests\Unit\Config;

use DateTime;
use MultiIvr\Config\Schedule\Schedule;
use MultiIvr\Config\Schedule\ScheduleDay;
use MultiIvr\Exceptions\MultiIvrException;
use PHPUnit\Framework\TestCase;

/**
 * Class ScheduleTest
 * @package MultiIvr\Tests\Unit\Config
 */
class ScheduleTest extends TestCase
{
    /**
     * @throws MultiIvrException
     */
    public function testCreate(): void
    {
        $days = [new ScheduleDay(4, [[60 * 12, 60 * 13]])];
        $schedule = new Schedule($days);
        self::assertFalse($schedule->isUse(DateTime::createFromFormat('Y-m-d H:i:s', '2021-02-17 12:00:00')));
        self::assertFalse($schedule->isUse(DateTime::createFromFormat('Y-m-d H:i:s', '2021-02-18 11:59:59')));
        self::assertTrue($schedule->isUse(DateTime::createFromFormat('Y-m-d H:i:s', '2021-02-18 12:00:00')));
        self::assertTrue($schedule->isUse(DateTime::createFromFormat('Y-m-d H:i:s', '2021-02-18 12:30:00')));
        self::assertTrue($schedule->isUse(DateTime::createFromFormat('Y-m-d H:i:s', '2021-02-18 12:59:59')));
        self::assertFalse($schedule->isUse(DateTime::createFromFormat('Y-m-d H:i:s', '2021-02-18 13:00:00')));
        self::assertFalse($schedule->isUse(DateTime::createFromFormat('Y-m-d H:i:s', '2021-02-19 12:00:00')));
        self::assertEquals($days, $schedule->getDays());
    }

    /**
     * @throws MultiIvrException
     */
    public function testCreateEmptyIntervals(): void
    {
        $schedule = new Schedule([new ScheduleDay(4)]);
        self::assertFalse($schedule->isUse(DateTime::createFromFormat('Y-m-d H:i:s', '2021-02-17 23:59:59')));
        self::assertTrue($schedule->isUse(DateTime::createFromFormat('Y-m-d H:i:s', '2021-02-18 00:00:00')));
        self::assertTrue($schedule->isUse(DateTime::createFromFormat('Y-m-d H:i:s', '2021-02-18 23:59:59')));
        self::assertFalse($schedule->isUse(DateTime::createFromFormat('Y-m-d H:i:s', '2021-02-19 00:00:00')));
    }

    /**
     * @throws MultiIvrException
     */
    public function testIncorrectIntervalType(): void
    {
        $this->expectException(MultiIvrException::class);
        $schedule = new Schedule([new ScheduleDay(4, [12])]);
        self::assertFalse($schedule->isUse(DateTime::createFromFormat('Y-m-d H:i:s', '2021-02-17 23:59:59')));
    }

    /**
     * @throws MultiIvrException
     */
    public function testIncorrectEmptyInterval(): void
    {
        $this->expectException(MultiIvrException::class);
        $schedule = new Schedule([new ScheduleDay(4, [[]])]);
        self::assertFalse($schedule->isUse(DateTime::createFromFormat('Y-m-d H:i:s', '2021-02-17 23:59:59')));
    }

    /**
     * @throws MultiIvrException
     */
    public function testIncorrectIntervalItemType(): void
    {
        $this->expectException(MultiIvrException::class);
        $schedule = new Schedule([new ScheduleDay(4, [['s', 12]])]);
        self::assertFalse($schedule->isUse(DateTime::createFromFormat('Y-m-d H:i:s', '2021-02-17 23:59:59')));
    }

    /**
     * @throws MultiIvrException
     */
    public function testIncorrectIntervalItemDataCount(): void
    {
        $this->expectException(MultiIvrException::class);
        $schedule = new Schedule([new ScheduleDay(4, [[12]])]);
        self::assertFalse($schedule->isUse(DateTime::createFromFormat('Y-m-d H:i:s', '2021-02-17 23:59:59')));
    }
}
