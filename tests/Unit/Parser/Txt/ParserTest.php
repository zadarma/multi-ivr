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
use MultiIvr\Parser\Txt\Parser;
use MultiIvr\Parser\Txt\Storage;
use PHPUnit\Framework\TestCase;

/**
 * Class ParserTest
 * @package MultiIvr\Tests\Unit\Parser\Txt
 */
class ParserTest extends TestCase
{
    /**
     * @throws ParserException
     * @throws MultiIvrException
     */
    public function testUnknownTag(): void
    {
        $config = <<<TXT
start default action=redirect action-target=100
menuitems default
TXT;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("Unknown tag 'menuitems', error in 2 line.");
        self::createParser()->parse($config);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testUnknownTagAttribute(): void
    {
        $config = <<<TXT
start default action=redirect action-target=100 unknown=1
TXT;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("Unknown 'unknown' attribute of the 'start' tag, error in the 1 line.");
        self::createParser()->parse($config);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testRequiredStartTagActionParameter(): void
    {
        $config = <<<TXT
start default action-target=100
TXT;
        $storageAttributeAction = Storage::ATTRIBUTE_ACTION;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("The '{$storageAttributeAction}' attribute of the 'start' tag is required, error in 1 line.");
        self::createParser()->parse($config);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testRequiredStartTagActionTargetParameter(): void
    {
        $config = <<<TXT
start default action=redirect
TXT;
        $storageAttributeActionTarget = Storage::ATTRIBUTE_ACTION_TARGET;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("The '{$storageAttributeActionTarget}' attribute of the 'start' tag is required, error in 1 line.");
        self::createParser()->parse($config);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testRepeatStartTagDefaultAttribute(): void
    {
        $storageTagStart = Storage::TAG_START;
        $storageAttributeDefault = Storage::ATTRIBUTE_DEFAULT;

        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("The '{$storageAttributeDefault}' attribute of '{$storageTagStart}' tag is already set, delete the duplicate '{$storageAttributeDefault}' attribute, error in 2 line.");
        $configText = <<<TXT
start default action=goto action-target=main
start default action=goto action-target=main
menu name=main playfile=file1
menu name=main default action=redirect action-target=101
TXT;
        self::createParser()->parse($configText);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testRequiredMenuTagNameParameter(): void
    {
        $config = <<<TXT
start default action=goto action-target=main
menu default action=redirect action-target=100
TXT;
        $storageTagMenu = Storage::TAG_MENU;
        $storageAttributeName = Storage::ATTRIBUTE_NAME;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("The '{$storageAttributeName}' attribute of the '{$storageTagMenu}' tag is required, error in 2 line.");
        self::createParser()->parse($config);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testRequiredMenuTagActionParameter(): void
    {
        $config = <<<TXT
start default action=goto action-target=main
menu name=main playfile=file1
menu name=main default action-target=100
TXT;
        $storageTagMenu = Storage::TAG_MENU;
        $storageAttributeAction = Storage::ATTRIBUTE_ACTION;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("The '{$storageAttributeAction}' attribute of the '{$storageTagMenu}' tag with name 'main' is required, error in 3 line.");
        self::createParser()->parse($config);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testRequiredMenuTagActionTargetParameter(): void
    {
        $config = <<<TXT
start default action=goto action-target=main
menu name=main playfile=file1
menu name=main default action=redirect
TXT;
        $storageTagMenu = Storage::TAG_MENU;
        $storageAttributeActionTarget = Storage::ATTRIBUTE_ACTION_TARGET;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("The '{$storageAttributeActionTarget}' attribute of the '{$storageTagMenu}' tag with name 'main' is required, error in 3 line.");
        self::createParser()->parse($config);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testRepeatMenuTagDefaultAttribute(): void
    {
        $storageTagMenu = Storage::TAG_MENU;
        $storageAttributeDefault = Storage::ATTRIBUTE_DEFAULT;

        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("The '{$storageAttributeDefault}' attribute of '{$storageTagMenu}' tag with name 'main' is already set, delete the duplicate '{$storageAttributeDefault}' attribute, error in 4 line.");
        $configText = <<<TXT
start default action=goto action-target=main
menu name=main playfile=file1
menu name=main default action=redirect action-target=101
menu name=main default action=redirect action-target=102
TXT;
        self::createParser()->parse($configText);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testRepeatMenuTagPlayFileAttribute(): void
    {
        $storageTagMenu = Storage::TAG_MENU;
        $storageAttributePlayFile = Storage::ATTRIBUTE_PLAY_FILE;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("The '{$storageAttributePlayFile}' attribute of '{$storageTagMenu}' tag with name 'main' is already set, delete the duplicate '{$storageAttributePlayFile}' attribute, error in 3 line.");
        $configText = <<<TXT
start default action=goto action-target=main
menu name=main playfile=file1
menu name=main playfile=file2
menu name=main default action=redirect action-target=100
TXT;
        self::createParser()->parse($configText);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testIncorrectMenuTimeOutAttribute(): void
    {
        $this->expectException(ParserException::class);
        $storageAttributeTimeOut = Storage::ATTRIBUTE_TIMEOUT;
        $this->expectExceptionMessage("The '{$storageAttributeTimeOut}' attribute must only include digit values, error in the 2 line.");
        $configText = <<<TXT
start default action=goto action-target=main
menu name=main playfile=file1 timeout=3s
menu name=main default action=redirect action-target=101
TXT;
        self::createParser()->parse($configText);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testRequiredScheduleTagNameParameter(): void
    {
        $config = <<<TXT
start schedule=test action=redirect action-target=100
start default action=redirect action-target=101
schedule data=mo,tu,we,th,fr:1700-1750,su
TXT;
        $storageAttributeName = Storage::ATTRIBUTE_NAME;
        $storageTagSchedule = Storage::TAG_SCHEDULE;

        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("The '{$storageAttributeName}' attribute of the '{$storageTagSchedule}' tag is required, error in 3 line.");
        self::createParser()->parse($config);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testRequiredScheduleTagDataParameter(): void
    {
        $config = <<<TXT
start schedule=test action=redirect action-target=100
start default action=redirect action-target=101
schedule name=test
TXT;
        $storageAttributeData = Storage::ATTRIBUTE_DATA;
        $storageTagSchedule = Storage::TAG_SCHEDULE;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("The '{$storageAttributeData}' attribute of the '{$storageTagSchedule}' tag is required, error in 3 line.");
        self::createParser()->parse($config);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testUnknownScheduleDay(): void
    {
        $config = <<<TXT
start schedule=test action=redirect action-target=100
start default action=redirect action-target=101
schedule name=test data=mon,tu,we,th,fr:1700-1750,su
TXT;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("Invalid 'mon' weekday, error in the 3 line.");
        self::createParser()->parse($config);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testIncorrectScheduleDayInterval(): void
    {
        $config = <<<TXT
start schedule=test action=redirect action-target=100
start default action=redirect action-target=101
schedule name=test data=mo,tu,we,th,fr:1700,su
TXT;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Invalid time interval, error in the 3 line.');
        self::createParser()->parse($config);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testIncorrectScheduleDayIntervalValueLength(): void
    {
        $config = <<<TXT
start schedule=test action=redirect action-target=100
start default action=redirect action-target=101
schedule name=test data=mo,tu,we,th,fr:1500-160,su
TXT;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Invalid time interval value, interval value length must be four digits, error in the 3 line.');
        self::createParser()->parse($config);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testIncorrectScheduleDayIntervalValue(): void
    {
        $config = <<<TXT
start schedule=test action=redirect action-target=100
start default action=redirect action-target=101
schedule name=test data=mo,tu,we,th,fr:16pm-17pm,su
TXT;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("Invalid time interval values, hours and minutes must be digits values, error in the 3 line.");
        self::createParser()->parse($config);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testIncorrectRewriteForwardNumberAttribute(): void
    {
        $config = <<<TXT
start default action=redirect action-target=100 rewrite-forward-number=+123
TXT;
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("Invalid 'rewrite-forward-number' attribute value, error in the 1 line.");
        self::createParser()->parse($config);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testConfigDuplicateAttributes(): void
    {
        $incorrectConfigText = <<<TXT
start default=0 default action=redirect action=goto action-target=100 action-target=main
menu name=main1 name=main playfile=file1 timeout=3 timeout=4 attempts=3 attempts=5 maxsymbols=3 maxsymbols=6
menu name=main callerid=09 callerid=11 calleddid=10 calleddid=12 schedule=test0 schedule=test1 action=goto action=redirect action-target=main action-target=101
menu name=main default=0 default action=goto action=redirect action-target=main action-target=101

schedule name=tr name=test data=mo,fr:1000-1100 data=mo,fr:1000-1200
schedule name=test1 data=mo,fr:1300-1400 data=mo,fr:1500-1600
TXT;
        $incorrectConfig = self::createParser()->parse($incorrectConfigText);
        $correctConfigText = <<<TXT
start default action=goto action-target=main
menu name=main playfile=file1 timeout=4 maxsymbols=6 attempts=5
menu name=main callerid=11 calleddid=12 schedule=test1 action=redirect action-target=101
menu name=main default action=redirect action-target=101

schedule name=test data=mo,fr:1000-1200
schedule name=test1 data=mo,fr:1500-1600
TXT;
        $correctConfig = self::createParser()->parse($correctConfigText);
        self::assertEquals($correctConfig, $incorrectConfig);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testParse(): void
    {
        $configTxt = <<<TXT
start callerid=111 action=redirect action-target=100 rewrite-forward-number=89631002323
start calleddid=112 action=redirect action-target=101
start schedule=off-the-clock callerid=113 calleddid=114 action=redirect action-target=102
start callerid=113 calleddid=114 action=redirect action-target=103
start default action=goto action-target=main

menu name=main playfile=file1 timeout=5 attempts=2 maxsymbols=3
menu name=main callerid=115,118 action=redirect action-target=104
menu name=main calleddid=116 action=redirect action-target=105
menu name=main button=1 schedule=dinner callerid=117 calleddid=118 action=redirect action-target=106
menu name=main button=1 callerid=117 calleddid=118 action=redirect action-target=107 rewrite-forward-number=89631002324
menu name=main default action=goto action-target=main.1

menu name=main.1 playfile=file2
menu name=main.1 default action=goto action-target=main

menu name=main.2 playfile=file3
menu name=main.2 default action=redirect action-target=108 rewrite-forward-number=89631002325

schedule name=dinner data=mo,tu,we,th,fr:1300-1400,su,sa
schedule name=off-the-clock data=mo,tu,we,th,fr:0000-0900;1800-2400,sa:0000-0900;1500-2400,su
TXT;
        $config = self::createParser()->parse($configTxt);

        $offTheClockScheduleWorkDaysInterval = [
            [0, 9 * 60],
            [18 * 60, 24 * 60],
        ];
        $offTheClockScheduleData = array_fill(1, 5, $offTheClockScheduleWorkDaysInterval) + [
                6 => [
                    [0, 9 * 60],
                    [15 * 60, 24 * 60],
                ],
                0 => [],
            ];
        $offTheClockSchedule = new Schedule(
            array_map(
                static function (int $dayNum, array $dayData) {
                    return new ScheduleDay($dayNum, $dayData);
                },
                array_keys($offTheClockScheduleData),
                $offTheClockScheduleData
            )
        );

        $dinnerScheduleWorkDaysInterval = [
            [13 * 60, 14 * 60],
        ];
        $dinnerScheduleData = array_fill(1, 5, $dinnerScheduleWorkDaysInterval) + [
                0 => [],
                6 => []
            ];
        $dinnerSchedule = new Schedule(
            array_map(
                static function (int $dayNum, array $dayData) {
                    return new ScheduleDay($dayNum, $dayData);
                },
                array_keys($dinnerScheduleData),
                $dinnerScheduleData
            )
        );

        $exceptedConfig = (new Config(
            (new Start(
                new Action(Action::ACTION_MENU, 'main')
            ))->addRule(
                new Rule(
                    new Action(
                        Action::ACTION_REDIRECT,
                        100,
                        Action::DEFAULT_RETURN_TIME_OUT,
                        [Action::EXTRA_OPTION_REWRITE_FORWARD_NUMBER => 89631002323]
                    ),
                    null,
                    [111]
                )
            )->addRule(
                new Rule(
                    new Action(Action::ACTION_REDIRECT, 101),
                    null,
                    [],
                    [112]
                )
            )->addRule(
                new Rule(
                    new Action(Action::ACTION_REDIRECT, 102),
                    null,
                    [113],
                    [114],
                    $offTheClockSchedule
                )
            )->addRule(
                new Rule(
                    new Action(Action::ACTION_REDIRECT, 103),
                    null,
                    [113],
                    [114]
                )
            )
        ))->addMenuItem(
            (new MenuItem(
                'main',
                'file1',
                new Action(Action::ACTION_MENU, 'main.1'),
                5,
                2,
                3
            ))->addRule(
                new Rule(
                    new Action(Action::ACTION_REDIRECT, 104),
                    null,
                    [115, 118]
                )
            )->addRule(
                new Rule(
                    new Action(Action::ACTION_REDIRECT, 105),
                    null,
                    [],
                    [116]
                )
            )->addRule(
                new Rule(
                    new Action(Action::ACTION_REDIRECT, 106),
                    1,
                    [117],
                    [118],
                    $dinnerSchedule
                )
            )->addRule(
                new Rule(
                    new Action(
                        Action::ACTION_REDIRECT,
                        107,
                        Action::DEFAULT_RETURN_TIME_OUT,
                        [Action::EXTRA_OPTION_REWRITE_FORWARD_NUMBER => 89631002324]
                    ),
                    1,
                    [117],
                    [118]
                )
            )
        )->addMenuItem(
            new MenuItem(
                'main.1',
                'file2',
                new Action(Action::ACTION_MENU, 'main')
            )
        )->addMenuItem(
            new MenuItem(
                'main.2',
                'file3',
                new Action(
                    Action::ACTION_REDIRECT,
                    108,
                    Action::DEFAULT_RETURN_TIME_OUT,
                    [Action::EXTRA_OPTION_REWRITE_FORWARD_NUMBER => 89631002325]
                )
            )
        );
        self::assertEquals($exceptedConfig, $config);
    }

    /**
     * @throws MultiIvrException
     * @throws ParserException
     */
    public function testSetOnlyPlayFileInRow(): void
    {
        $invalidConfigText = <<<TXT
start default action=goto action-target=main
menu name=main playfile=file1 default action=redirect action-target=100
menu name=main default action=redirect action-target=101
TXT;
        $parserConfig = self::createParser()->parse($invalidConfigText);
        $validConfigText = <<<TXT
start default action=goto action-target=main
menu name=main playfile=file1
menu name=main default action=redirect action-target=101
TXT;
        $exceptedParserConfig = self::createParser()->parse($validConfigText);
        self::assertEquals($exceptedParserConfig, $parserConfig);
    }

    /**
     * @return Parser
     */
    private static function createParser(): Parser
    {
        return new Parser();
    }
}
