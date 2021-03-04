<?php

namespace MultiIvr\Tests\Unit\Config;

use MultiIvr\Config\Action;
use MultiIvr\Exceptions\MultiIvrException;
use PHPUnit\Framework\TestCase;

/**
 * Class ActionTest
 * @package MultiIvr\Tests\Unit\Config
 */
class ActionTest extends TestCase
{
    /**
     * @throws MultiIvrException
     */
    public function testCreateException(): void
    {
        $this->expectException(MultiIvrException::class);
        new Action('test', 100);
    }

    /**
     * @throws MultiIvrException
     */
    public function testSuccessCreate(): void
    {
        foreach (Action::ALLOW_ACTION_TYPES as $type) {
            $action = new Action($type, 100, 3);
            self::assertEquals($type, $action->getType());
            self::assertEquals(100, $action->getTarget());
            self::assertEquals(3, $action->getReturnTimeOut());
        }
    }
}
