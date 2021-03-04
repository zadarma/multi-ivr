<?php

namespace MultiIvr\Parser\Txt;

use MultiIvr\Config\Action;
use MultiIvr\Config\Config;
use MultiIvr\Config\Rule;
use MultiIvr\Config\MenuItem;
use MultiIvr\Config\Schedule\Schedule;
use MultiIvr\Config\Schedule\ScheduleDay;
use MultiIvr\Config\Start;
use MultiIvr\Exceptions\MultiIvrException;
use MultiIvr\Exceptions\ParserException;

/**
 * Class ConfigBuilder
 * @package MultiIvr\Parser\Txt
 */
class ConfigBuilder
{
    /**
     * @var Schedule[]
     */
    private $schedules = [];

    /**
     * @var Start
     */
    private $start;

    /**
     * @var MenuItem[]
     */
    private $menuItems = [];

    /**
     * @param array $start
     * @param array $menuItems
     * @param array $scheduleItems
     * @return Config
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function build(array $start, array $menuItems, array $scheduleItems): Config
    {
        $this->createSchedules($scheduleItems);
        $this->createStart($start);
        $this->createMenuItems($menuItems);
        $config = new Config($this->start);
        foreach ($this->menuItems as $menuItem) {
            $config->addMenuItem($menuItem);
        }
        return $config;
    }

    /**
     * @param array $start
     * @throws MultiIvrException
     * @throws ParserException
     */
    private function createStart(array $start): void
    {
        $this->validationStart($start);
        $defaultAction = $start[Storage::BUILDER_DEFAULT_ACTION];
        $this->start = new Start(
            new Action(
                $defaultAction[Storage::ATTRIBUTE_ACTION],
                $defaultAction[Storage::ATTRIBUTE_ACTION_TARGET]
            )
        );
        if (!empty($start[Storage::BUILDER_RULES])) {
            foreach ($start[Storage::BUILDER_RULES] as $rule) {
                $this->start->addRule($this->createFilter($rule));
            }
        }
    }

    /**
     * @param array $menuItems
     * @throws MultiIvrException
     * @throws ParserException
     */
    private function createMenuItems(array $menuItems): void
    {
        foreach ($menuItems as $menuItemName => $menuItemData) {
            $this->validationMenuItem($menuItemName, $menuItemData);
            $defaultAction = $menuItemData[Storage::BUILDER_DEFAULT_ACTION];
            $menuItemObject = new MenuItem(
                $menuItemName,
                $menuItemData[Storage::BUILDER_MENU_ITEM_PLAY_FILE],
                new Action(
                    $defaultAction[Storage::ATTRIBUTE_ACTION],
                    $defaultAction[Storage::ATTRIBUTE_ACTION_TARGET]
                ),
                $menuItemData[Storage::BUILDER_MENU_ITEM_TIMEOUT] ?? MenuItem::TIMEOUT,
                $menuItemData[Storage::BUILDER_MENU_ITEM_ATTEMPTS] ?? MenuItem::ATTEMPTS,
                $menuItemData[Storage::BUILDER_MENU_ITEM_MAX_SYMBOLS] ?? MenuItem::MAX_SYMBOLS
            );
            if (!empty($menuItemData[Storage::BUILDER_RULES])) {
                foreach ($menuItemData[Storage::BUILDER_RULES] as $rule) {
                    $menuItemObject->addRule($this->createFilter($rule));
                }
            }
            $this->menuItems[$menuItemName] = $menuItemObject;
        }
    }

    /**
     * @param string $menuItemName
     * @param array $menuItem
     * @throws ParserException
     */
    private function validationMenuItem(string $menuItemName, array $menuItem): void
    {
        $storageMenuTag = Storage::TAG_MENU;
        $storageDefaultAttribute = Storage::ATTRIBUTE_DEFAULT;
        $storagePlayFileAttribute = Storage::ATTRIBUTE_PLAY_FILE;
        if (empty($menuItem[Storage::BUILDER_DEFAULT_ACTION])) {
            throw new ParserException("One of the '{$storageMenuTag}' tags with '{$menuItemName}' name must have a '{$storageDefaultAttribute}' attribute.");
        }
        if (empty($menuItem[Storage::BUILDER_MENU_ITEM_PLAY_FILE])) {
            throw new ParserException("One of the '{$storageMenuTag}' tags with '{$menuItemName}' name must have a '{$storagePlayFileAttribute}' attribute.");
        }
    }

    /**
     * @param array $start
     * @throws ParserException
     */
    private function validationStart(array $start): void
    {
        $storageStartTag = Storage::TAG_START;
        if (empty($start)) {
            throw new ParserException("The '{$storageStartTag}' tag is required.");
        }
        $storageDefaultAttribute = Storage::ATTRIBUTE_DEFAULT;
        if (empty($start[Storage::BUILDER_DEFAULT_ACTION])) {
            throw new ParserException("One of the '{$storageStartTag}' tags must have a '{$storageDefaultAttribute}' attribute.");
        }
    }

    /**
     * @param string|null $scheduleName
     * @return Schedule|null
     * @throws ParserException
     */
    private function getSchedule(?string $scheduleName): ?Schedule
    {
        if ($scheduleName === null) {
            return null;
        }
        $scheduleTag = Storage::TAG_SCHEDULE;
        if (!isset($this->schedules[$scheduleName])) {
            throw new ParserException("The '{$scheduleTag}' tag with '{$scheduleName}' name not found.");
        }
        return $this->schedules[$scheduleName];
    }

    /**
     * @param array $rule
     * @return Rule
     * @throws MultiIvrException
     * @throws ParserException
     */
    private function createFilter(array $rule): Rule
    {
        return new Rule(
            new Action(
                $rule[Storage::ATTRIBUTE_ACTION],
                $rule[Storage::ATTRIBUTE_ACTION_TARGET]
            ),
            $rule[Storage::ATTRIBUTE_BUTTON] ?? null,
            $rule[Storage::ATTRIBUTE_CALLER_ID] ?? [],
            $rule[Storage::ATTRIBUTE_CALLED_DID] ?? [],
            $this->getSchedule($rule[Storage::ATTRIBUTE_SCHEDULE] ?? null)
        );
    }


    /**
     * @param array $scheduleItems
     * @throws MultiIvrException
     */
    private function createSchedules(array $scheduleItems): void
    {
        foreach ($scheduleItems as $name => $scheduleDays) {
            $scheduleDaysObjects = [];
            foreach ($scheduleDays as $dayNum => $minutesInterval) {
                $scheduleDaysObjects[] = new ScheduleDay($dayNum, $minutesInterval);
            }
            $this->schedules[$name] = new Schedule($scheduleDaysObjects);
        }
    }
}
