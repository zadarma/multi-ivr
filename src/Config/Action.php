<?php

namespace MultiIvr\Config;

use MultiIvr\Exceptions\MultiIvrException;

/**
 * Class Action
 * @package MultiIvr\Config
 */
class Action
{
    public const ACTION_MENU = 'goto';
    public const ACTION_REDIRECT = 'redirect';
    public const DEFAULT_RETURN_TIME_OUT = 0;
    public const EXTRA_OPTION_REWRITE_FORWARD_NUMBER = 'rewrite_forward_number';

    public const ALLOW_ACTION_TYPES = [
        self::ACTION_MENU,
        self::ACTION_REDIRECT,
    ];
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $target;

    /**
     * @var int
     */
    private $returnTimeOut;

    /**
     * @var array
     */
    private $extraOptions;

    /**
     * Action constructor.
     * @param string $type
     * @param string $target
     * @param int $returnTimeOut
     * @param array $extraOptions
     * @throws MultiIvrException
     */
    public function __construct(
        string $type,
        string $target,
        int $returnTimeOut = self::DEFAULT_RETURN_TIME_OUT,
        array $extraOptions = []
    ) {
        $this->type = $type;
        if (!in_array($type, self::ALLOW_ACTION_TYPES, true)) {
            throw new MultiIvrException("Unknown action '{$type}'");
        }
        $this->target = $target;
        $this->returnTimeOut = $returnTimeOut;
        $this->extraOptions = $extraOptions;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @return int
     */
    public function getReturnTimeOut(): int
    {
        return $this->returnTimeOut;
    }

    /**
     * @return array
     */
    public function getExtraOptions(): array
    {
        return $this->extraOptions;
    }
}
