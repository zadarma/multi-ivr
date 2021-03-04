<?php

namespace MultiIvr\Config;

use DateTimeInterface;
use MultiIvr\Config\Schedule\Schedule;

/**
 * Class Command
 * @package MultiIvr\Config
 */
class Rule
{
    /**
     * @var Action
     */
    private $action;

    /**
     * @var String[]
     */
    private $callerIds;

    /**
     * @var String[]
     */
    private $calledDids;

    /**
     * @var Schedule|null
     */
    private $schedule;

    /**
     * @var string|null
     */
    private $button;

    /**
     * Rule constructor.
     * @param Action $action
     * @param string|null $button
     * @param String[] $callerIds
     * @param String[] $calledDids
     * @param Schedule|null $schedule
     */
    public function __construct(
        Action $action,
        ?string $button = null,
        array $callerIds = [],
        array $calledDids = [],
        ?Schedule $schedule = null
    ) {
        $this->action = $action;
        foreach ($callerIds as $id) {
            $id = $this->cleanPhoneNumber($id);
            if ($id !== '') {
                $this->callerIds[$id] = $id;
            }
        }
        foreach ($calledDids as $did) {
            $did = $this->cleanPhoneNumber($did);
            if ($did !== '') {
                $this->calledDids[$did] = $did;
            }
        }
        $this->schedule = $schedule;
        $this->button = $button;
    }

    public function isUse(
        string $callerId,
        string $calledDid,
        DateTimeInterface $dateTime,
        ?string $button = null
    ): bool {
        if (!empty($this->callerIds) && !$this->hasCallerId($callerId)) {
            return false;
        }
        if (!empty($this->calledDids) && !$this->hasCalledDid($calledDid)) {
            return false;
        }
        if ($this->button !== null && $this->button !== $button) {
            return false;
        }
        if ($this->schedule !== null && !$this->schedule->isUse($dateTime)) {
            return false;
        }
        return true;
    }

    /**
     * @return Action
     */
    public function getAction(): Action
    {
        return $this->action;
    }

    /**
     * @return String[]
     */
    public function getCallerIds(): array
    {
        return array_values($this->callerIds);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function hasCallerId(string $id): bool
    {
        return $this->hasId($id, true);
    }

    /**
     * @param string $did
     * @return bool
     */
    public function hasCalledDid(string $did): bool
    {
        return $this->hasId($did, false);
    }

    /**
     * @return String[]
     */
    public function getCalledDids(): array
    {
        return array_values($this->calledDids);
    }

    /**
     * @return Schedule
     */
    public function getSchedule(): ?Schedule
    {
        return $this->schedule;
    }

    /**
     * @return string|null
     */
    public function getButton(): ?string
    {
        return $this->button;
    }

    /**
     * @param string|null $phoneNumber
     * @return string
     */
    private function cleanPhoneNumber(?string $phoneNumber): string
    {
        if ($phoneNumber === null) {
            return '';
        }
        return preg_replace('/\D/', '', $phoneNumber);
    }

    /**
     * @param string $id
     * @param bool $isCallerId
     * @return bool
     */
    private function hasId(string $id, bool $isCallerId): bool
    {
        $id = $this->cleanPhoneNumber($id);
        if ($id === '') {
            return false;
        }
        return $isCallerId ? isset($this->callerIds[$id]) : isset($this->calledDids[$id]);
    }
}
