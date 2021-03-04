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
     * Action constructor.
     * @param string $type
     * @param string $target
     * @param int $returnTimeOut
     * @throws MultiIvrException
     */
    public function __construct(string $type, string $target, int $returnTimeOut = 0)
    {
        $this->type = $type;
        if (!in_array($type, self::ALLOW_ACTION_TYPES, true)) {
            throw new MultiIvrException("Unknown action '{$type}'");
        }
        $this->target = $target;
        $this->returnTimeOut = $returnTimeOut;
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
}
