<?php

namespace MultiIvr\Tests\Unit\Config;

use MultiIvr\Config\Action;
use MultiIvr\Config\Rule;
use MultiIvr\Config\Start;
use MultiIvr\Exceptions\MultiIvrException;
use PHPUnit\Framework\TestCase;

/**
 * Class StartTest
 * @package MultiIvr\Tests\Unit\Config
 */
class StartTest extends TestCase
{
    /**
     * @throws MultiIvrException
     */
    public function testProperties(): void
    {
        $action = new Action(Action::ACTION_REDIRECT, 100);
        $start = new Start($action);
        self::assertEquals($action, $start->getDefaultAction());
        self::assertEquals([], $start->getRules());

        $rule = new Rule(new Action(Action::ACTION_MENU, 'main'));
        $start->addRule($rule);
        self::assertEquals([$rule], $start->getRules());
    }
}
