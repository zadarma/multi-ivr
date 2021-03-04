<?php

namespace MultiIvr\Config;

/**
 * Class MenuItem
 * @package MultiIvr\Config
 */
class MenuItem
{
    public const TIMEOUT = 3;
    public const ATTEMPTS = 1;
    public const MAX_SYMBOLS = 1;

    /**
     * @var string|null
     */
    private $playFileId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $timeOut;

    /**
     * @var Rule[]
     */
    private $rules = [];

    /**
     * @var Action
     */
    private $defaultAction;

    /**
     * @var int
     */
    private $attempts;

    /**
     * @var int
     */
    private $maxSymbols;

    /**
     * MenuItem constructor.
     * @param string $name
     * @param string $playFileId
     * @param Action $defaultAction
     * @param int $timeOut
     * @param int $attempts
     * @param int $maxSymbols
     */
    public function __construct(
        string $name,
        string $playFileId,
        Action $defaultAction,
        int $timeOut = self::TIMEOUT,
        int $attempts = self::ATTEMPTS,
        int $maxSymbols = self::MAX_SYMBOLS
    ) {
        $this->playFileId = $playFileId;
        $this->name = $name;
        $this->timeOut = $timeOut;
        $this->attempts = $attempts;
        $this->maxSymbols = $maxSymbols;
        $this->defaultAction = $defaultAction;
    }

    /**
     * @param Rule $rule
     * @return $this
     */
    public function addRule(Rule $rule): self
    {
        $this->rules[] = $rule;
        return $this;
    }

    /**
     * @return Action
     */
    public function getDefaultAction(): Action
    {
        return $this->defaultAction;
    }

    /**
     * @return string|null
     */
    public function getPlayFileId(): ?string
    {
        return $this->playFileId;
    }

    /**
     * @return int
     */
    public function getTimeOut(): int
    {
        return $this->timeOut;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Rule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @return int
     */
    public function getAttempts(): int
    {
        return $this->attempts;
    }

    /**
     * @return int
     */
    public function getMaxSymbols(): int
    {
        return $this->maxSymbols;
    }
}
