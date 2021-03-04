<?php

namespace MultiIvr\Tests\Unit\Parser\Txt;

use MultiIvr\Config\Action;
use MultiIvr\Config\Config;
use MultiIvr\Config\Rule;
use MultiIvr\Config\MenuItem;
use MultiIvr\Config\Schedule\Schedule;
use MultiIvr\Config\Schedule\ScheduleDay;
use MultiIvr\Config\Start;
use MultiIvr\Exceptions\MultiIvrException;
use MultiIvr\Exceptions\ParserException;
use MultiIvr\Parser\Txt\ConfigBuilder;
use MultiIvr\Parser\Txt\Storage;
use PHPUnit\Framework\TestCase;

/**
 * Class TxtParserTest
 * @package MultiIvr\Tests\Unit\Parser
 */
class ConfigBuilderTest extends TestCase
{
    /**
     * @throws ParserException
     * @throws MultiIvrException
     */
    public function testNotFoundStartTag(): void
    {
        $storageStartTag = Storage::TAG_START;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("The '{$storageStartTag}' tag is required");
        (new ConfigBuilder())->build([], [], []);
    }

    /**
     * @throws ParserException
     * @throws MultiIvrException
     */
    public function testNotFoundStartDefaultAction(): void
    {
        $storageStartTag = Storage::TAG_START;
        $storageDefaultAttribute = Storage::ATTRIBUTE_DEFAULT;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("One of the '{$storageStartTag}' tags must have a '{$storageDefaultAttribute}' attribute.");
        (new ConfigBuilder())->build(
            [
                Storage::BUILDER_RULES => [
                    [
                        Storage::ATTRIBUTE_ACTION => Action::ACTION_REDIRECT,
                        Storage::ATTRIBUTE_ACTION_TARGET => 100,
                    ]
                ]
            ],
            [],
            []
        );
    }

    /**
     * @throws ParserException
     * @throws MultiIvrException
     */
    public function testNotFoundMenuItemDefaultAction(): void
    {
        $storageMenuTag = Storage::TAG_MENU;
        $storageDefaultAttribute = Storage::ATTRIBUTE_DEFAULT;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("One of the '{$storageMenuTag}' tags with 'main' name must have a '{$storageDefaultAttribute}' attribute.");
        (new ConfigBuilder())->build(
            [
                Storage::BUILDER_DEFAULT_ACTION => [
                    Storage::ATTRIBUTE_ACTION => Action::ACTION_MENU,
                    Storage::ATTRIBUTE_ACTION_TARGET => 'main',
                ]
            ],
            ['main' => [Storage::ATTRIBUTE_PLAY_FILE => 'file1',]],
            []
        );
    }

    /**
     * @throws ParserException
     * @throws MultiIvrException
     */
    public function testNotFoundMenuItemPlayFile(): void
    {
        $storageMenuTag = Storage::TAG_MENU;
        $storagePlayFileAttribute = Storage::ATTRIBUTE_PLAY_FILE;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("One of the '{$storageMenuTag}' tags with 'main' name must have a '{$storagePlayFileAttribute}' attribute.");
        (new ConfigBuilder())->build(
            [
                Storage::BUILDER_DEFAULT_ACTION => [
                    Storage::ATTRIBUTE_ACTION => Action::ACTION_MENU,
                    Storage::ATTRIBUTE_ACTION_TARGET => 'main',
                ]
            ],
            [
                'main' => [
                    Storage::BUILDER_DEFAULT_ACTION => [
                        Storage::ATTRIBUTE_ACTION => Action::ACTION_REDIRECT,
                        Storage::ATTRIBUTE_ACTION_TARGET => 100
                    ],
                ]
            ],
            []
        );
    }

    /**
     * @throws ParserException
     * @throws MultiIvrException
     */
    public function testNotFoundStartSchedule(): void
    {
        $scheduleTag = Storage::TAG_SCHEDULE;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("The '{$scheduleTag}' tag with 'work' name not found.");
        (new ConfigBuilder())->build(
            [
                Storage::BUILDER_DEFAULT_ACTION => [
                    Storage::ATTRIBUTE_ACTION => Action::ACTION_REDIRECT,
                    Storage::ATTRIBUTE_ACTION_TARGET => 100,
                ],
                Storage::BUILDER_RULES => [
                    [
                        Storage::ATTRIBUTE_ACTION => Action::ACTION_REDIRECT,
                        Storage::ATTRIBUTE_ACTION_TARGET => 200,
                        Storage::ATTRIBUTE_SCHEDULE => 'work'
                    ]
                ]
            ],
            [],
            []
        );
    }

    /**
     * @throws ParserException
     * @throws MultiIvrException
     */
    public function testNotFoundMenuItemSchedule(): void
    {
        $scheduleTag = Storage::TAG_SCHEDULE;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("The '{$scheduleTag}' tag with 'work' name not found.");
        (new ConfigBuilder())->build(
            [
                Storage::BUILDER_DEFAULT_ACTION => [
                    Storage::ATTRIBUTE_ACTION => Action::ACTION_MENU,
                    Storage::ATTRIBUTE_ACTION_TARGET => 'main',
                ],
            ],
            [
                'main' => [
                    Storage::ATTRIBUTE_PLAY_FILE => 'file1',
                    Storage::BUILDER_DEFAULT_ACTION => [
                        Storage::ATTRIBUTE_ACTION => Action::ACTION_REDIRECT,
                        Storage::ATTRIBUTE_ACTION_TARGET => 110,
                    ],
                    Storage::BUILDER_RULES => [
                        [
                            Storage::ATTRIBUTE_ACTION => Action::ACTION_REDIRECT,
                            Storage::ATTRIBUTE_ACTION_TARGET => 200,
                            Storage::ATTRIBUTE_SCHEDULE => 'work'
                        ]
                    ]
                ]
            ],
            []
        );
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testBuildConfig(): void
    {
        $callerId = [111];
        $callerDid = [112];
        $start = [
            Storage::BUILDER_DEFAULT_ACTION => [
                Storage::ATTRIBUTE_ACTION => Action::ACTION_REDIRECT,
                Storage::ATTRIBUTE_ACTION_TARGET => 100,
            ],
            Storage::BUILDER_RULES => [
                [
                    Storage::ATTRIBUTE_SCHEDULE => 'work',
                    Storage::ATTRIBUTE_CALLER_ID => $callerId,
                    Storage::ATTRIBUTE_CALLED_DID => $callerDid,
                    Storage::ATTRIBUTE_ACTION => Action::ACTION_MENU,
                    Storage::ATTRIBUTE_ACTION_TARGET => 'main',
                ],
            ],
        ];
        $menuItems = [
            'main' => [
                Storage::ATTRIBUTE_PLAY_FILE => 'file1',
                Storage::BUILDER_DEFAULT_ACTION => [
                    Storage::ATTRIBUTE_ACTION => Action::ACTION_MENU,
                    Storage::ATTRIBUTE_ACTION_TARGET => 'main.1',
                ],
                Storage::BUILDER_RULES => [
                    [
                        Storage::ATTRIBUTE_SCHEDULE => 'work',
                        Storage::ATTRIBUTE_BUTTON => 1,
                        Storage::ATTRIBUTE_CALLER_ID => $callerId,
                        Storage::ATTRIBUTE_CALLED_DID => $callerDid,
                        Storage::ATTRIBUTE_ACTION => Action::ACTION_REDIRECT,
                        Storage::ATTRIBUTE_ACTION_TARGET => 101,
                    ],
                ],
            ],
            'main.1' => [
                Storage::ATTRIBUTE_PLAY_FILE => 'file2',
                Storage::BUILDER_DEFAULT_ACTION => [
                    Storage::ATTRIBUTE_ACTION => Action::ACTION_REDIRECT,
                    Storage::ATTRIBUTE_ACTION_TARGET => 102,
                ],
                Storage::BUILDER_RULES => [
                    [
                        Storage::ATTRIBUTE_SCHEDULE => 'work',
                        Storage::ATTRIBUTE_BUTTON => 2,
                        Storage::ATTRIBUTE_CALLER_ID => $callerId,
                        Storage::ATTRIBUTE_CALLED_DID => $callerDid,
                        Storage::ATTRIBUTE_ACTION => Action::ACTION_REDIRECT,
                        Storage::ATTRIBUTE_ACTION_TARGET => 103,
                    ],
                ],
            ],
        ];
        $workMinutesIntervals = [
            [9 * 60, 13 * 60],
            [14 * 60, 18 * 60]
        ];
        $schedules = [
            'work' => array_fill(1, 5, $workMinutesIntervals)
        ];
        $buildConfig = (new ConfigBuilder())->build($start, $menuItems, $schedules);

        $schedule = new Schedule(
            array_map(
                static function (int $dayNum, array $dayData) {
                    return new ScheduleDay($dayNum, $dayData);
                },
                array_keys($schedules['work']),
                $schedules['work']
            )
        );

        $exceptedConfig = (new Config(
            (new Start(
                new Action(
                    Action::ACTION_REDIRECT,
                    100
                )
            ))->addRule(
                new Rule(
                    new Action(
                        Action::ACTION_MENU,
                        'main'
                    ),
                    null,
                    $callerId,
                    $callerDid,
                    $schedule
                )
            )
        ))->addMenuItem(
            (new MenuItem(
                'main',
                'file1',
                new Action(Action::ACTION_MENU, 'main.1')
            ))->addRule(
                new Rule(
                    new Action(
                        Action::ACTION_REDIRECT,
                        101
                    ),
                    1,
                    $callerId,
                    $callerDid,
                    $schedule
                )
            )
        )->addMenuItem(
            (new MenuItem(
                'main.1',
                'file2',
                new Action(Action::ACTION_REDIRECT, 102)
            ))->addRule(
                new Rule(
                    new Action(
                        Action::ACTION_REDIRECT,
                        103
                    ),
                    2,
                    $callerId,
                    $callerDid,
                    $schedule
                )
            )
        );
        self::assertEquals($exceptedConfig, $buildConfig);
    }
}
