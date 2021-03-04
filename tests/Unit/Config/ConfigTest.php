<?php

namespace MultiIvr\Tests\Unit\Config;

use MultiIvr\Config\Action;
use MultiIvr\Config\Config;
use MultiIvr\Config\Rule;
use MultiIvr\Config\MenuItem;
use MultiIvr\Config\Start;
use MultiIvr\Exceptions\MultiIvrException;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigTest
 * @package MultiIvr\Tests\Unit\Config
 */
class ConfigTest extends TestCase
{
    /**
     * @throws MultiIvrException
     */
    public function testStartUnknownTargetMenuItem(): void
    {
        $this->expectException(MultiIvrException::class);
        $this->expectExceptionMessage("Menu item with name 'main' not found.");
        $config = new Config(new Start(new Action(Action::ACTION_MENU, 'main')));
        $config->validation();
    }

    /**
     * @throws MultiIvrException
     */
    public function testMenuItemUnknownTargetMenuItem(): void
    {
        $this->expectException(MultiIvrException::class);
        $this->expectExceptionMessage("Menu item with name 'main.1' not found.");
        $config = new Config(new Start(new Action(Action::ACTION_MENU, 'main')));
        $config->addMenuItem(
            new MenuItem('main', 'fileId', new Action(Action::ACTION_MENU, 'main.1'))
        );
        $config->validation();
    }

    /**
     * @throws MultiIvrException
     */
    public function testAddRepeatMenuItem(): void
    {
        $this->expectException(MultiIvrException::class);
        $this->expectExceptionMessage("Menu item with 'main' name already exists");
        $config = new Config(new Start(new Action(Action::ACTION_MENU, 'main')));
        $menuItem = new MenuItem('main', 'fileId', new Action(Action::ACTION_REDIRECT, 100));
        $config->addMenuItem($menuItem)->addMenuItem($menuItem);
    }

    /**
     * @throws MultiIvrException
     */
    public function testConfig(): void
    {
        $start = new Start(new Action(Action::ACTION_MENU, 'main'));
        $start->addRule(new Rule(new Action(Action::ACTION_REDIRECT, 100), null, [111]));
        $mainMenuItem = new MenuItem('main', 'fileId', new Action(Action::ACTION_MENU, 'main.1'));
        $mainMenuItem->addRule(new Rule(new Action(Action::ACTION_MENU, 'main.1'), 1));
        $menuSubItem = new MenuItem('main.1', 'fileId', new Action(Action::ACTION_REDIRECT, 100));
        $config = new Config($start);
        $config->addMenuItem($mainMenuItem)->addMenuItem($menuSubItem)->validation();

        self::assertTrue($config->hasMenuItem('main'));
        self::assertEquals($mainMenuItem, $config->getMenuItemByName('main'));
        self::assertTrue($config->hasMenuItem('main.1'));
        self::assertEquals($menuSubItem, $config->getMenuItemByName('main.1'));
        self::assertFalse($config->hasMenuItem('main.2'));

        self::assertEquals([$mainMenuItem, $menuSubItem], $config->getMenuItems());
        self::assertEquals($start, $config->getStart());
    }
}
