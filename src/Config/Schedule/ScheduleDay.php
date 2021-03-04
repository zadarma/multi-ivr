<?php

namespace MultiIvr\Config\Schedule;

use MultiIvr\Exceptions\MultiIvrException;

/**
 * Class ScheduleDay
 * @package MultiIvr\Confg\Schedule
 */
class ScheduleDay
{
    /**
     * sunday - 0
     * monday - 1
     * tuesday - 2
     * ...
     * saturday - 6
     *
     * @var int
     */
    private $dayNum;

    /**
     * @var array
     */
    private $minutesIntervals = [];

    /**
     * ScheduleDay constructor.
     * @param int $dayNum
     * @param array $minutesIntervals
     * @throws MultiIvrException
     */
    public function __construct(int $dayNum, array $minutesIntervals = [])
    {
        if ($dayNum < 0 || $dayNum > 6) {
            throw new MultiIvrException('Invalid weekday number, weekday number must be from 0 to 6.');
        }
        $this->dayNum = $dayNum;

        foreach ($minutesIntervals as $interval) {
            if (!is_array($interval)) {
                throw new MultiIvrException('The minutes interval must consist of two integer values.');
            }
            $this->minutesIntervals[] = self::getValidMinutesInterval($interval);
        }
    }

    /**
     * @return int
     */
    public function getDayNum(): int
    {
        return $this->dayNum;
    }

    /**
     * @return array
     */
    public function getMinutesIntervals(): array
    {
        return $this->minutesIntervals;
    }

    /**
     * @param array $minutesInterval
     * @return int[]
     * @throws MultiIvrException
     */
    private static function getValidMinutesInterval(array $minutesInterval): array
    {
        $minutesInterval = array_pad($minutesInterval, 2, null);
        [$from, $to] = $minutesInterval;
        if (!is_int($from) || !is_int($to)) {
            throw new MultiIvrException('The minutes interval must consist of two integer values.');
        }
        return [$from, $to];
    }
}
