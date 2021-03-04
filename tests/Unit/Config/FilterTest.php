<?php

namespace MultiIvr\Tests\Unit\Config;

use DateTime;
use MultiIvr\Config\Action;
use MultiIvr\Config\Rule;
use MultiIvr\Config\Schedule\Schedule;
use MultiIvr\Config\Schedule\ScheduleDay;
use MultiIvr\Exceptions\MultiIvrException;
use PHPUnit\Framework\TestCase;

/**
 * Class FilterTest
 * @package MultiIvr\Tests\Unit\Config
 */
class FilterTest extends TestCase
{
    /**
     * @throws MultiIvrException
     */
    public function testProperties(): void
    {
        $action = new Action(Action::ACTION_MENU, 'main');
        $button = 1;
        $callerId = ['+100', '102'];
        $calledDid = ['101', '110'];
        $schedule = new Schedule([new ScheduleDay(4, [[60 * 12, 60 * 13]])]);
        $rule = new Rule($action, $button, $callerId, $calledDid, $schedule);
        self::assertEquals($action, $rule->getAction());
        self::assertEquals(1, $rule->getButton());
        self::assertEquals([100, 102], $rule->getCallerIds());
        self::assertEquals([101, 110], $rule->getCalledDids());
        self::assertEquals($schedule, $rule->getSchedule());
    }

    /**
     * @throws MultiIvrException
     */
    public function testIsUse(): void
    {
        $action = new Action(Action::ACTION_MENU, 'main');
        $button = 1;
        $callerId = 100;
        $calledDid = 101;
        $schedule = new Schedule([new ScheduleDay(4, [[60 * 12, 60 * 13]])]);

        $valuesMap = [
            [$button, [$callerId], [$calledDid], $schedule],
            [null, [$callerId], [$calledDid], $schedule],
            [$button, [], [$calledDid], $schedule],
            [$button, [$callerId], [], $schedule],
            [$button, [$callerId], [$calledDid], null]
        ];

        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', '2021-02-18 12:00:00');

        foreach ($valuesMap as $values) {
            [$currentBtn, $currentCallerId, $currentCalledDid, $currentSchedule] = $values;
            $rule = new Rule($action, $currentBtn, $currentCallerId, $currentCalledDid, $currentSchedule);
            self::assertTrue($rule->isUse($callerId, $calledDid, $dateTime, $button));
            self::assertEquals(empty($currentCallerId), $rule->isUse(101, $calledDid, $dateTime, $button));
            self::assertEquals(empty($currentCalledDid), $rule->isUse($callerId, 102, $dateTime, $button));
            self::assertEquals(
                $currentSchedule === null,
                $rule->isUse(
                    $callerId,
                    $calledDid,
                    DateTime::createFromFormat('Y-m-d H:i:s', '2021-02-18 11:00:00'),
                    $button
                )
            );
            self::assertEquals(
                $currentBtn === null,
                $rule->isUse($callerId, $calledDid, $dateTime, 0)
            );
        }
    }
}
