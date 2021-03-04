<?php

namespace MultiIvr\Tests\Unit\Config;

use MultiIvr\Config\Action;
use MultiIvr\Config\Rule;
use MultiIvr\Config\MenuItem;
use MultiIvr\Exceptions\MultiIvrException;
use PHPUnit\Framework\TestCase;

/**
 * Class MenuItemTest
 * @package MultiIvr\Tests\Unit\Config
 */
class MenuItemTest extends TestCase
{
    /**
     * @throws MultiIvrException
     */
    public function testProperties(): void
    {
        $props = ['name', 'fileId', new Action(Action::ACTION_REDIRECT, 100), 4, 3, 2];
        $menuItem = new MenuItem(...$props);
        self::assertEquals(
            $props,
            [
                $menuItem->getName(),
                $menuItem->getPlayFileId(),
                $menuItem->getDefaultAction(),
                $menuItem->getTimeOut(),
                $menuItem->getAttempts(),
                $menuItem->getMaxSymbols()
            ]
        );
        self::assertEquals([], $menuItem->getRules());
        $rule = new Rule(new Action(Action::ACTION_MENU, 'main'));
        $menuItem->addRule($rule);

        self::assertEquals([$rule], $menuItem->getRules());
    }
}
