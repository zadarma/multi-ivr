# Interactive voice menu
PHP library for Zadarma voice menu setup

*Read the description in other languages:*
* [Spanish](README_ES.md)
* [German](README_DE.md)
* [Polish](README_PL.md)
* [Russian](README_RU.md)
* [Ukrainian](README_UA.md)
* [French](README_FR.md)

## Requirements:
* PHP >= 7.2.0
* cURL
* TLS v1.2
* php-mbstring
## Setup
Add a correlation `zadarma/multi-ivr` to the composer.json file of your project:
```
composer require zadarma/multi-ivr
```

## Features

Voice menu flexible configuration using filters and rules has been implemented.
Four types of filters are supported:
* by caller number;
* by called number;
* filter by schedule;
* user action.

A rule consists of filters and actions. An action will be performed if rule filter requirements match.
There are two types of actions - transfer to the menu (audio file is played and user action is awaited) and call transfer to a phone number or a scenario (calling several numbers or blacklist).

We will take a closer look at the features below.
## Use

To receive call notifications, you need to create an open for all access link that will receive POST-requests with information from Zadarma system.
This link has to be input in the [personal account](https://my.zadarma.com/api/) under the title "Call notifications in PBX".

The following php code needs to be placed at the link:
```php
<?php

use MultiIvr\MultiIvr;

if (isset($_GET['zd_echo'])) {
    exit($_GET['zd_echo']);
}

require_once 'vendor/autoload.php';
$key = 'Your api key';
$secret = 'Your api secret';
$ivrMenuConfig = 'your config';
MultiIvr::default()->handle($key, $secret, $ivrMenuConfig);
```

`$key` and `$secret` - API interface authorization keys, they have to be received in the [personal account](https://my.zadarma.com/api/).
`$ivrMenuConfig` - voice menu text config, we will talk about it later.

## Config example

Task:

Divide clients into 3 groups using voice menu:

1. Direct calls to the sales department when pressing 1.
2. Direct calls to the purchasing department when pressing 2.
3. Direct calls to the support department when pressing 3.
4. If a caller stays on the line, the call is transferred to the secretary.

Here are the voice menu rules:
```
start default action=goto action-target=main

menu name=main playfile=file1
menu name=main button=1 action=redirect action-target=100
menu name=main button=2 action=redirect action-target=101
menu name=main button=3 action=redirect action-target=102
menu name=main default action=redirect action-target=103
```

## Voice menu config syntax

Config has 3 possible tags - start, menu, schedule. Each tag has its allowed attributes, they are described below.
Attribute name and value are divided by the equal sign `attribute=attributeValue`.

Attributes are divided by spaces, that is why spaces cannot be used in attribute values.

### Start tag

Required. It sets the rules for the beginning of an incoming call to PBX.

It has the following attributes:

1. action - action type. 

    2 possible values: 
    * redirect - transfer to a scenario or PBX extension number;
    * goto - transfer to the voice menu by its name.

2. action-target - action purpose. 

    If action attribute has the value:
    * redirect
    
       An extension number or scenario id must be entered.
    
       Scenarios are created in the [personal account](https://my.zadarma.com/mypbx/in_calls/). 
       Scenario id is set in the 0-1 format, where 0 - voice menu number, 1 - scenario number.
    
       Scenario can also have a blacklist value - in this case, the call will be declined with a busy signal.
    
    * goto
    
       Voice menu name must be entered.
        
3. callerid - filter by caller numbers, separated by comma. 
4. calleddid - filter by called numbers, separated by comma. 
5. default - default action. One of the actions must have a default sign.
6. schedule - schedule name filter, can be used to set working and lunch hours.

**Start example with several filters:**

```
start default action=redirect action-target=100
start callerid=111 calleddid=112 action=redirect action-target=blacklist 
start callerid=111 action=redirect action-target=101
start calleddid=112 action=redirect action-target=102 
```

* first rule - if number 111 calls number 112, call is transferred to the blacklist scenario (decline call with the busy signal)
* if the first rule was not applied, the following one is checked;
* if no rules were applied, the default action is performed - call transfer to number 100.


### Menu tag
Sets voice menu elements, rules for transferring between them, rules for transferring to scenarios or PBX extension numbers. 

It has the same attributes as start, as well as:

1. name - voice menu element name, required attribute.
2. playfile - played file id, required attribute.

   Entered once for the voice menu in a separate from the filters line, responsible for the played file of the voice menu. The id of the uploaded or voiced file can be found in the [personal account](https://my.zadarma.com/mypbx/in_calls/).
3. timeout - input timeout in seconds.

   Entered in the same line as playfile attribute, default value - 3.
4. attempts - number of played file repetitions, if the caller has not pressed a button.

   Entered in the same line as playfile attribute, default value - 1.
5. maxsymbols - maximum number of symbols that the system is waiting to be entered.
   
   Entered in the same line as playfile attribute, default value - 1.
6. button - filter for pressing one or several buttons.

### Schedule tag

1. name - schedule name.
2. data - schedule description.

Schedule example:
```
schedule name=work data=mo,tu,we,th,fr:0800-1300;1400-1700,sa:0900-1500
```
Schedule has name work, it is set that from Monday to Friday the time is from 08:00 until 12:59:59 and from 14:00 until 16:59:59, on Saturday from 09:00 until 14:59:59.

Possible days of the week labels: **su, mo, tu, we, th, fr, sa**.

Time can be set for each day of the week as one or more intervals separated by commas.
An interval consists of two values separated by a hyphen - start time and end time. Start and end time must consist of four digits.
The first two digits stand for hours, the other two for minutes.

Time can also be set for several days by listing weekdays using a comma and time using a colon, like so `mo,tu,we,th,fr:0800-1300;1400-1700`.

If the time is not set, the whole day is applied from 00:00:00 until 23:59:59. 
For example, `mo,tu,we,th,fr:0800-1300;1400-1700,sa,su` mo,tu,we,th,fr have two time intervals from 8:00 until 12:59:59 and from 14:00:00 until 16:59:59, and sa,su have one time interval from 00:00:00 until 23:59:59. 

Schedule example:
```
schedule name=relax data=mo,tu,we,th,fr:1300-1400,sa,su
```
The schedule name is relax, it is set that Mondays through Fridays the off time is from 13:00:00 until 13:59:59, and Saturdays and Sundays are off.

### Rules

Config lines that include filters in their attributes are assigned to rules.

As discussed above, the following attributes belong to filters: callerid, calleddid, button, schedule.
Rules describe the conditions for performing certain actions depending on 
 Caller number, number that is being called, user action and current time.

**Rules priority is set by the order of the lines.**

**Rules example:**

```
...
menu name=main button=1 callerid=111 calleddid=112 schedule=dinner action=redirect action-target=101
menu name=main button=1 callerid=111 action=redirect action-target=102
menu name=main button=1 action=redirect action-target=103
menu name=main default action=redirect action-target=104
...

schedule name=dinner data=mo,tu,we,th,fr:1300-1400
```
* first rule - if number 111 called number 112, pressed 1 during lunch time, the call is transferred to number 101;
* if the first rule was not applied, the following one is checked;
* if no rules were applied, the default action is performed - call transfer to number 104.

## Multilevel voice menu example:
```
start callerid=111,222 action=redirect action-target=blacklist
start schedule=off-the-clock action=goto action-target=off-the-clock
start schedule=dinner action=goto action-target=dinner
start callerid=112,223 action=goto action-target=main.1
start default action=goto action-target=main

menu name=main playfile=43d8a740ec032766 timeout=5 attempts=2 maxsymbols=2
menu name=main button=1 action=goto action-target=main.1
menu name=main button=2 action=goto action-target=main.2
menu name=main button=3 action=redirect action-target=0-1
menu name=main button=4 action=redirect action-target=101
menu name=main button=10 action=redirect action-target=110
menu name=main default action=redirect action-target=100

menu name=main.1 playfile=a279dd3a1da19e57
menu name=main.1 button=1 action=redirect action-target=0-2
menu name=main.1 button=2 action=redirect action-target=102
menu name=main.1 button=* action=goto action-target=main
menu name=main.1 default action=goto action-target=main.1

menu name=main.2 playfile=a6842305f1996e34
menu name=main.2 button=1 action=redirect action-target=0-3
menu name=main.2 button=2 action=redirect action-target=103
menu name=main.2 button=* action=goto action-target=main
menu name=main.2 default action=goto action-target=main.2

menu name=dinner playfile=facdfc7ca2029552
menu name=dinner default action=redirect action-target=0-4

menu name=off-the-clock playfile=b1b2d11f59d8d208
menu name=off-the-clock default action=redirect action-target=blacklist

schedule name=dinner data=mo,tu,we,th,fr:1300-1400
schedule name=off-the-clock data=mo,tu,we,th,fr:0000-0900;1800-2400,sa,su
```

1. start
    * if number 111 or 222 is calling, the call is transferred to the blacklist scenario (hang up);
    * otherwise if the off-the-clock schedule condition is met, the call is transferred to the off-the-clock menu
    * otherwise if the dinner schedule condition is met, the call is transferred to the dinner menu;
    * otherwise is number 112 or 223 is calling, the call is transferred to the main.1 menu;
    * otherwise the call is transferred to the main menu.
2. main menu
    * 43d8a740ec032766 file is played, input timeout - 5,
    File playing repetitions if the caller has not pressed a button - 2, 
    the maximum number of symbols that can be entered - 2.
    * if 1 is pressed, the call is transferred to main.1 menu;
    * otherwise if 2 is pressed, the call is transferred to main.2 menu;
    * otherwise if 3 is pressed, the call is transferred to scenario 0-1;
    * otherwise if 4 is pressed, the call is transferred to PBX extension number 101;
    * otherwise if 10 is pressed, the call is transferred to PBX extension number 110;
    * otherwise the call is transferred to PBX extension number 100.
3. main.1 menu
    * a279dd3a1da19e57 file is played;
    * if 1 is pressed, the call is transferred to scenario 0-2;
    * otherwise if 2 is pressed, the call is transferred to PBX extension number 102;
    * otherwise if * is pressed, the call is transferred to the main menu;
    * otherwise menu.1 is repeated.
4. main.2 menu
    * a6842305f1996e34 file is played;
    * if 1 is pressed, the call is transferred to scenario 0-3;
    * otherwise if 2 is pressed, the call is transferred to PBX extension number 103;
    * otherwise if * is pressed, the call is transferred to the main menu;
    * otherwise menu.2 is repeated.
5. dinner menu
    * facdfc7ca2029552 file is played;
    * the call is transferred to scenario 0-4.
6. off-the-clock menu
    * b1b2d11f59d8d208 file is played;
    * the call is transferred to the blacklist scenario(hang up).
7. dinner schedule
    * Condition: Monday - Friday from 13:00:00 until 13:59:59;
8. off-the-clock schedule
    * Condition: Monday - Friday from 00:00:00 until 08:59:59, from 18:00:00 until 23:59:59, Saturday, Sunday.