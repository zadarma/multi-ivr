<?php

namespace MultiIvr\Config\Schedule;

use DateTime;
use DateTimeInterface;

/**
 * Class Schedule
 * @package MultiIvr\Config\Schedule
 */
class Schedule
{
    private const ALL_DAY_MINUTES_INTERVAL = [0, 24 * 60];

    /**
     * @var ScheduleDay[]
     */
    private $days = [];

    /**
     * Schedule constructor.
     * @param array $days
     */
    public function __construct(array $days)
    {
        foreach ($days as $oneDay) {
            $this->days[$oneDay->getDayNum()] = $oneDay;
        }
    }

    /**
     * @return ScheduleDay[]
     */
    public function getDays(): array
    {
        return array_values($this->days);
    }

    /**
     * @param DateTimeInterface $dateTime
     * @return bool
     */
    public function isUse(DateTimeInterface $dateTime): bool
    {
        $numOfDay = $dateTime->format('w');
        if (!isset($this->days[$numOfDay])) {
            return false;
        }
        $day = $this->days[$numOfDay];
        $currentSecondsInterval = $dateTime->getTimeStamp() - (new DateTime($dateTime->format('Y-m-d 00:00:00')))->getTimeStamp();
        $dayMinutesInterval = $day->getMinutesIntervals();
        if (empty($dayMinutesInterval)) {
            $dayMinutesInterval = [self::ALL_DAY_MINUTES_INTERVAL];
        }
        foreach ($dayMinutesInterval as $intervalMinutes) {
            [$from, $to] = $intervalMinutes;
            if ($from * 60 <= $currentSecondsInterval && $currentSecondsInterval <= $to * 60 - 1) {
                return true;
            }
        }
        return false;
    }
}
