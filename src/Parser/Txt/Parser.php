<?php

namespace MultiIvr\Parser\Txt;

use MultiIvr\Config\Config;
use MultiIvr\Exceptions\MultiIvrException;
use MultiIvr\Exceptions\ParserException;
use MultiIvr\Parser\ConfigParser;
use SplStack;

/**
 * Class Parser
 * @package MultiIvr\Parser\Txt
 */
class Parser implements ConfigParser
{
    /**
     * @var array
     */
    private $startData = [];

    /**
     * @var array
     */
    private $menuItems = [];

    /**
     * @var array
     */
    private $scheduleItems = [];

    /**
     * @var int
     */
    private $currentLine = 1;

    /**
     * @param string $inputConfigTxt
     * @return Config
     * @throws ParserException
     * @throws MultiIvrException
     */
    public function parse(string $inputConfigTxt): Config
    {
        $this->reset();
        $lines = preg_split('/\r\n|\r|\n/', mb_strtolower($inputConfigTxt));
        foreach ($lines as $lineNum => $line) {
            $this->currentLine = $lineNum + 1;
            $clearLine = trim(preg_replace('/\s+/', ' ', $line));
            if (empty($clearLine)) {
                continue;
            }
            $lineAttributesAr = explode(' ', $clearLine);
            $tag = array_shift($lineAttributesAr);
            $this->addTagAttributes($tag, $lineAttributesAr);
        }
        return (new ConfigBuilder())->build($this->startData, $this->menuItems, $this->scheduleItems);
    }

    /**
     *
     */
    private function reset(): void
    {
        $this->startData = $this->menuItems = $this->scheduleItems = [];
        $this->currentLine = 0;
    }

    /**
     * @param string $tag
     * @param array $attributes
     * @throws ParserException
     */
    private function addTagAttributes(string $tag, array $attributes): void
    {
        switch ($tag) {
            case Storage::TAG_START:
                $this->addStartTagAttributes($tag, $attributes);
                break;
            case Storage::TAG_MENU:
                $this->addMenuDataAttributes($tag, $attributes);
                break;
            case Storage::TAG_SCHEDULE:
                $this->addScheduleDataAttributes($tag, $attributes);
                break;
            default:
                self::throwParserException("Unknown tag '%s', error in {$this->currentLine} line.", [$tag]);
        }
    }

    /**
     * @param string $tag
     * @param array $attributes
     * @throws ParserException
     */
    private function addStartTagAttributes(string $tag, array $attributes): void
    {
        $rule = [];
        $isDefault = false;
        foreach ($attributes as $oneAttribute) {
            [$attributeName, $attributeValue] = self::parseAttribute($oneAttribute);
            $this->validationTagAttribute($tag, $attributeName);
            if ($attributeName === Storage::ATTRIBUTE_DEFAULT) {
                $isDefault = $this->parseAttributeValue($attributeValue, $attributeName);
            } else {
                $rule[$attributeName] = $this->parseAttributeValue($attributeValue, $attributeName);
            }
        }
        $this->validateStartRule($rule);
        if ($isDefault) {
            if (empty($this->startData[Storage::BUILDER_DEFAULT_ACTION])) {
                $this->startData[Storage::BUILDER_DEFAULT_ACTION] = $rule;
            } else {
                self::throwParserException(
                    "The '%s' attribute of '%s' tag is already set, delete the duplicate '%s' attribute, error in {$this->currentLine} line.",
                    [Storage::ATTRIBUTE_DEFAULT, Storage::TAG_START, Storage::ATTRIBUTE_DEFAULT]
                );
            }
        } else {
            $this->startData[Storage::BUILDER_RULES][] = $rule;
        }
    }

    /**
     * @param array $rule
     * @throws ParserException
     */
    private function validateStartRule(array $rule): void
    {
        $msgTemplate = "The '%s' attribute of the '%s' tag is required, error in {$this->currentLine} line.";
        if (empty($rule[Storage::ATTRIBUTE_ACTION])) {
            self::throwParserException($msgTemplate, [Storage::ATTRIBUTE_ACTION, Storage::TAG_START]);
        }
        if (empty($rule[Storage::ATTRIBUTE_ACTION_TARGET])) {
            self::throwParserException($msgTemplate, [Storage::ATTRIBUTE_ACTION_TARGET, Storage::TAG_START]);
        }
    }

    /**
     * @param string $tag
     * @param array $attributes
     * @throws ParserException
     */
    private function addMenuDataAttributes(string $tag, array $attributes): void
    {
        $rule = [];
        $defaultFilter = null;
        $menuItemName = null;
        $isDefault = false;
        $menuItemPropertiesMap = [
            Storage::ATTRIBUTE_PLAY_FILE => [null, Storage::BUILDER_MENU_ITEM_PLAY_FILE],
            Storage::ATTRIBUTE_ATTEMPTS => [null, Storage::BUILDER_MENU_ITEM_ATTEMPTS],
            Storage::ATTRIBUTE_MAX_SYMBOLS => [null, Storage::BUILDER_MENU_ITEM_MAX_SYMBOLS],
            Storage::ATTRIBUTE_TIMEOUT => [null, Storage::BUILDER_MENU_ITEM_TIMEOUT],
        ];
        foreach ($attributes as $oneAttribute) {
            [$attributeName, $attributeValue] = self::parseAttribute($oneAttribute);
            $this->validationTagAttribute($tag, $attributeName);
            switch ($attributeName) {
                case Storage::ATTRIBUTE_DEFAULT:
                    $isDefault = $this->parseAttributeValue(
                        $attributeValue,
                        $attributeName
                    );
                    break;
                case Storage::ATTRIBUTE_PLAY_FILE:
                case Storage::ATTRIBUTE_ATTEMPTS:
                case Storage::ATTRIBUTE_MAX_SYMBOLS:
                case Storage::ATTRIBUTE_TIMEOUT:
                    $menuItemPropertiesMap[$attributeName][0] = $this->parseAttributeValue(
                        $attributeValue,
                        $attributeName
                    );
                    break;
                case Storage::ATTRIBUTE_NAME:
                    $menuItemName = $attributeValue;
                    break;
                default:
                    $rule[$attributeName] = $this->parseAttributeValue($attributeValue, $attributeName);
                    break;
            }
        }
        if (!isset($menuItemName)) {
            self::throwParserException(
                "The '%s' attribute of the '%s' tag is required, error in {$this->currentLine} line.",
                [Storage::ATTRIBUTE_NAME, Storage::TAG_MENU]
            );
        }
        $menuItem = $this->menuItems[$menuItemName] ?? [];
        $isSetProperty = false;
        $duplicateExceptionMsg = "The '%s' attribute of '%s' tag with name '%s' is already set, delete the duplicate '%s' attribute, error in {$this->currentLine} line.";
        foreach ($menuItemPropertiesMap as $propertyAttributeName => $propertyInfo) {
            [$propertyValue, $menuItemPropertyName] = $propertyInfo;
            if (isset($menuItem[$menuItemPropertyName]) && $propertyValue !== null) {
                self::throwParserException(
                    $duplicateExceptionMsg,
                    [$propertyAttributeName, Storage::TAG_MENU, $menuItemName, $propertyAttributeName]
                );
            }
            if ($propertyValue !== null) {
                $menuItem[$menuItemPropertyName] = $propertyValue;
                $isSetProperty = true;
            }
        }
        if (!$isSetProperty) {
            if ($isDefault) {
                $this->validateMenuRule($menuItemName, $rule);
                if (!empty($menuItem[Storage::BUILDER_DEFAULT_ACTION])) {
                    self::throwParserException(
                        $duplicateExceptionMsg,
                        [Storage::ATTRIBUTE_DEFAULT, Storage::TAG_MENU, $menuItemName, Storage::ATTRIBUTE_DEFAULT]
                    );
                }
                $this->validateMenuRule($menuItemName, $rule);
                $menuItem[Storage::BUILDER_DEFAULT_ACTION] = $rule;
            } elseif ($rule) {
                $this->validateMenuRule($menuItemName, $rule);
                $menuItem[Storage::BUILDER_RULES][] = $rule;
            }
        }
        $this->menuItems[$menuItemName] = $menuItem;
    }

    /**
     * @param string|null $attributeValue
     * @param string $attributeName
     * @return bool|int|string|null|array
     * @throws ParserException
     */
    private function parseAttributeValue(?string $attributeValue, string $attributeName)
    {
        switch ($attributeName) {
            case Storage::ATTRIBUTE_DEFAULT:
                return (bool)($attributeValue ?? true);
            case Storage::ATTRIBUTE_TIMEOUT:
                if (!ctype_digit($attributeValue)) {
                    self::throwParserException(
                        "The '%s' attribute must only include digit values, error in the {$this->currentLine} line.",
                        [$attributeName]
                    );
                }
                return (int)$attributeValue;
            case Storage::ATTRIBUTE_CALLER_ID:
            case Storage::ATTRIBUTE_CALLED_DID:
                return $attributeValue !== null ? array_filter(
                    explode(',', $attributeValue),
                    static function (string $value) {
                        return $value !== '';
                    }
                ) : [];
            case Storage::ATTRIBUTE_REWRITE_FORWARD_NUMBER:
                if (!preg_match('/^(\+*\d{5,})$/', $attributeValue)) {
                    self::throwParserException(
                        "Invalid '%s' attribute value, error in the {$this->currentLine} line.",
                        [$attributeName]
                    );
                }
            default:
                return $attributeValue;
        }
    }

    /**
     * @param string $menuItemName
     * @param array $rule
     * @throws ParserException
     */
    private function validateMenuRule(string $menuItemName, array $rule): void
    {
        $msgTemplate = "The '%s' attribute of the '%s' tag with name '%s' is required, error in {$this->currentLine} line.";
        if (empty($rule[Storage::ATTRIBUTE_ACTION])) {
            self::throwParserException(
                $msgTemplate,
                [Storage::ATTRIBUTE_ACTION, Storage::TAG_MENU, $menuItemName]
            );
        }
        if (empty($rule[Storage::ATTRIBUTE_ACTION_TARGET])) {
            self::throwParserException(
                $msgTemplate,
                [Storage::ATTRIBUTE_ACTION_TARGET, Storage::TAG_MENU, $menuItemName]
            );
        }
    }

    /**
     * @param string $tag
     * @param array $attributes
     * @throws ParserException
     */
    private function addScheduleDataAttributes(string $tag, array $attributes): void
    {
        $msgTemplate = "The '%s' attribute of the '%s' tag is required, error in {$this->currentLine} line.";
        $scheduleName = null;
        $scheduleData = null;
        foreach ($attributes as $oneAttribute) {
            [$attributeName, $attributeValue] = self::parseAttribute($oneAttribute);
            $this->validationTagAttribute($tag, $attributeName);
            if ($attributeName === Storage::ATTRIBUTE_NAME) {
                $scheduleName = $attributeValue;
            } else {
                $scheduleData = $attributeValue;
            }
        }
        if ($scheduleName === null) {
            self::throwParserException(
                $msgTemplate,
                [Storage::ATTRIBUTE_NAME, Storage::TAG_SCHEDULE]
            );
        }
        $scheduleDays = [];
        if ($scheduleData !== null) {
            $scheduleDays = $this->parseScheduleData($scheduleData);
        }
        if (empty($scheduleDays)) {
            self::throwParserException(
                $msgTemplate,
                [Storage::ATTRIBUTE_DATA, Storage::TAG_SCHEDULE]
            );
        }
        $this->scheduleItems[$scheduleName] = $scheduleDays;
    }

    /**
     * @param string $tag
     * @param string $attributeName
     * @throws ParserException
     */
    private function validationTagAttribute(string $tag, string $attributeName): void
    {
        if (!Storage::isKnownTagAttribute($tag, $attributeName)) {
            self::throwParserException(
                "Unknown '%s' attribute of the '%s' tag, error in the {$this->currentLine} line.",
                [$attributeName, $tag]
            );
        }
    }

    /**
     * @param string $data
     * @return array
     * @throws ParserException
     */
    private function parseScheduleData(string $data): array
    {
        $scheduleResult = [];
        $daysDataAr = explode(',', $data);
        $stack = new SplStack();
        foreach ($daysDataAr as $dayData) {
            [$day, $schedule] = self::parseDaySchedule($dayData);
            $dayNum = Storage::getWeekDayNum($day);
            if ($dayNum === null) {
                self::throwParserException(
                    "Invalid '%s' weekday, error in the {$this->currentLine} line.",
                    [$day]
                );
            }
            if (!$schedule) {
                $stack->push($dayNum);
            } else {
                $scheduleDateTimeInterval = $this->parseDayTimeInterval($schedule);
                while (!$stack->isEmpty()) {
                    $prevDayNum = $stack->shift();
                    $scheduleResult[$prevDayNum] = $scheduleDateTimeInterval;
                }
                $scheduleResult[$dayNum] = $scheduleDateTimeInterval;
            }
        }
        while (!$stack->isEmpty()) {
            $prevDayNum = $stack->pop();
            $scheduleResult[$prevDayNum] = [];
        }
        return $scheduleResult;
    }

    /**
     * @param string $dayIntervalsStr
     * @return array
     * @throws ParserException
     */
    private function parseDayTimeInterval(string $dayIntervalsStr): array
    {
        $result = [];
        $dayIntervalsAr = explode(';', $dayIntervalsStr) ?: [];
        foreach ($dayIntervalsAr as $dayIntervalStr) {
            $intervalAr = explode('-', $dayIntervalStr) ?: [];
            if (count($intervalAr) !== 2) {
                self::throwParserException("Invalid time interval, error in the {$this->currentLine} line.");
            }
            [$from, $to] = $intervalAr;
            $result[] = [$this->parseMinutesIntervalValue($from), $this->parseMinutesIntervalValue($to)];
        }
        return $result;
    }

    /**
     * @param string $attribute
     * @return array
     */
    private static function parseAttribute(string $attribute): array
    {
        $data = explode('=', $attribute);
        return array_pad($data, 2, null);
    }

    /**
     * @param string $daysStr
     * @return array
     */
    private static function parseDaySchedule(string $daysStr): array
    {
        $data = explode(':', $daysStr) ?: [];
        return array_pad($data, 2, null);
    }

    /**
     * @param string $value
     * @return int
     * @throws ParserException
     */
    private function parseMinutesIntervalValue(string $value): int
    {
        if (mb_strlen($value) < 4) {
            self::throwParserException("Invalid time interval value, interval value length must be four digits, error in the {$this->currentLine} line.");
        }
        $hours = mb_substr($value, 0, 2);
        $minutes = mb_substr($value, 2, 2);

        if (!ctype_digit($hours) || !ctype_digit($minutes)) {
            self::throwParserException("Invalid time interval values, hours and minutes must be digits values, error in the {$this->currentLine} line.");
        }
        return $hours * 60 + $minutes;
    }

    /**
     * @param string $text
     * @param mixed ...$arguments
     * @throws ParserException
     */
    private static function throwParserException(string $text, array $arguments = []): void
    {
        throw new ParserException(empty($arguments) ? $text : sprintf($text, ...$arguments));
    }
}
