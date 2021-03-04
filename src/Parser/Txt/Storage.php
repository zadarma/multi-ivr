<?php

namespace MultiIvr\Parser\Txt;

/**
 * Class Storage
 * @package MultiIvr\Parser\Txt
 */
class Storage
{
    public const TAG_START = 'start';
    public const TAG_MENU = 'menu';
    public const TAG_SCHEDULE = 'schedule';

    public const ATTRIBUTE_ACTION = 'action';
    public const ATTRIBUTE_ACTION_TARGET = 'action-target';
    public const ATTRIBUTE_CALLER_ID = 'callerid';
    public const ATTRIBUTE_CALLED_DID = 'calleddid';
    public const ATTRIBUTE_DEFAULT = 'default';
    public const ATTRIBUTE_SCHEDULE = 'schedule';
    public const ATTRIBUTE_BUTTON = 'button';
    public const ATTRIBUTE_PLAY_FILE = 'playfile';
    public const ATTRIBUTE_NAME = 'name';
    public const ATTRIBUTE_DATA = 'data';
    public const ATTRIBUTE_TIMEOUT = 'timeout';
    public const ATTRIBUTE_ATTEMPTS = 'attempts';
    public const ATTRIBUTE_MAX_SYMBOLS = 'maxsymbols';

    private const WEEK_DAYS = [
        'su' => 0,
        'mo' => 1,
        'tu' => 2,
        'we' => 3,
        'th' => 4,
        'fr' => 5,
        'sa' => 6,
    ];

    private const KNOWN_ATTRIBUTES = [
        self::TAG_START => [
            self::ATTRIBUTE_ACTION,
            self::ATTRIBUTE_ACTION_TARGET,
            self::ATTRIBUTE_CALLER_ID,
            self::ATTRIBUTE_CALLED_DID,
            self::ATTRIBUTE_DEFAULT,
            self::ATTRIBUTE_SCHEDULE,
        ],
        self::TAG_MENU => [
            self::ATTRIBUTE_ACTION,
            self::ATTRIBUTE_ACTION_TARGET,
            self::ATTRIBUTE_CALLER_ID,
            self::ATTRIBUTE_CALLED_DID,
            self::ATTRIBUTE_DEFAULT,
            self::ATTRIBUTE_SCHEDULE,
            self::ATTRIBUTE_BUTTON,
            self::ATTRIBUTE_PLAY_FILE,
            self::ATTRIBUTE_NAME,
            self::ATTRIBUTE_TIMEOUT,
            self::ATTRIBUTE_ATTEMPTS,
            self::ATTRIBUTE_MAX_SYMBOLS,
        ],
        self::TAG_SCHEDULE => [
            self::ATTRIBUTE_NAME,
            self::ATTRIBUTE_DATA,
        ]
    ];

    public const BUILDER_RULES = 'rules';
    public const BUILDER_DEFAULT_ACTION = 'defaultAction';
    public const BUILDER_MENU_ITEM_PLAY_FILE = 'playfile';
    public const BUILDER_MENU_ITEM_ATTEMPTS = 'attempts';
    public const BUILDER_MENU_ITEM_MAX_SYMBOLS = 'maxsymbols';
    public const BUILDER_MENU_ITEM_TIMEOUT = 'timeout';

    /**
     * @param $tag
     * @return bool
     */
    public static function isKnownTag(string $tag): bool
    {
        return isset(self::KNOWN_ATTRIBUTES[$tag]);
    }

    /**
     * @param string $tag
     * @param string $attribute
     * @return bool
     */
    public static function isKnownTagAttribute(string $tag, string $attribute): bool
    {
        static $flippedKnownAttributes;

        if (!self::isKnownTag($tag)) {
            return false;
        }
        $knownTagAttributes = self::KNOWN_ATTRIBUTES[$tag];
        if (!isset($flippedKnownAttributes[$tag])) {
            $flippedKnownAttributes[$tag] = array_flip($knownTagAttributes);
        }
        return isset($flippedKnownAttributes[$tag][$attribute]);
    }

    /**
     * @param string $dayName
     * @return int|null
     */
    public static function getWeekDayNum(string $dayName): ?int
    {
        return self::WEEK_DAYS[$dayName] ?? null;
    }
}
