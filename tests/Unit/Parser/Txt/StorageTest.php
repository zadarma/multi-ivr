<?php

namespace MultiIvr\Tests\Unit\Parser\Txt;

use MultiIvr\Parser\Txt\Storage;
use PHPUnit\Framework\TestCase;

/**
 * Class StorageTest
 * @package MultiIvr\Tests\Unit\Parser\Txt
 */
class StorageTest extends TestCase
{
    /**
     *
     */
    public function testIsKnownTag(): void
    {
        self::assertTrue(Storage::isKnownTag(Storage::TAG_MENU));
        self::assertTrue(Storage::isKnownTag(Storage::TAG_START));
        self::assertTrue(Storage::isKnownTag(Storage::TAG_SCHEDULE));
        self::assertFalse(Storage::isKnownTag('test'));
    }

    /**
     *
     */
    public function testIsKnownTagAttribute(): void
    {
        self::assertTrue(Storage::isKnownTagAttribute(Storage::TAG_MENU, Storage::ATTRIBUTE_ACTION));
        self::assertFalse(Storage::isKnownTagAttribute('test', Storage::ATTRIBUTE_ACTION));
        self::assertFalse(Storage::isKnownTagAttribute(Storage::TAG_MENU, 'test'));
    }
}
