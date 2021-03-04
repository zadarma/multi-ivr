# Interaktives Sprachmenü
PHP-Bibliothek für Einstellung des Zadarma-Sprachmenüs

*Lesen Sie die Beschreibung in anderen Sprachen:*
* [Englisch](README.md)
* [Spanisch](README_ES.md)
* [Polnisch](README_PL.md)
* [Russisch](README_RU.md)
* [Ukrainisch](README_UA.md)
* [Französisch](README_FR.md)

## Anforderungen:
* PHP >= 7.2.0
* cURL
* TLS v1.2
* php-mbstring
## Installation
Fügen Sie die Abhängigkeit `zadarma/multi-ivr` zur Datei composer.json Ihres Projekts hinzu:
```
composer require zadarma/multi-ivr
```

## Funktionen

Die Möglichkeit einer flexiblen Konfiguration des Sprachmenüs mithilfe von Filtern und Regeln wurde implementiert.
Es werden vier Arten von Filtern unterstützt:
* nach die Nummer des Anrufers;
* nach die Nummer, die angerufen wird;
* Filter nach Zeitplan;
* Benutzeraktion.

Eine Regel besteht aus Filtern und einer Aktion. Die Aktion wird ausgeführt, wenn die Bedingungen der Regelfilter übereinstimmen.
Es gibt zwei Arten von Aktionen: Aufrufen des Menüs (eine Audiodatei wird abgespielt und eine Benutzeraktion wird erwartet) und Weiterleiten eines Anrufs an eine Telefonnummer oder ein Szenario (mehrere Nummern oder Blacklist anrufen).

Wir werden alle Möglichkeiten im Folgenden genauer betrachten.
## Anwendung

Um Anrufbenachrichtigungen zu erhalten, müssen Sie einen öffentlich zugänglichen Link erstellen, der POST-Anfragen mit Informationen aus dem Zadarma-System empfängt.
Dieser Link muss in [Ihrem Profile](https://my.zadarma.com/api/) im Bereich "Benachrichtigungen über PBX-Anrufe" angegeben werden.

Der Link sollte den folgenden PHP-Code enthalten:
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

`$key` und `$secret` - sind Schlüssel für die Autorisierung in der API-Oberfläche. Sie müssen in [Ihrem Profile](https://my.zadarma.com/api/)abgerufen werden.
`$ivrMenuConfig` - Textkonfiguration des Sprachmenüs, wir werden es unten genauer beschreiben.

## Konfigurationsbeispiel

Aufgabe:

Über das Sprachmenü müssen die Kunden in drei Gruppen geteilt werden:

1. Durch Drücken der Taste 1 werden die Anrufe direkt an die Verkaufsabteilung gesendet.
2. Durch Drücken der Taste 2 werden die Anrufe direkt an die Einkaufsabteilung gesendet.
3. Durch Drücken der Taste 3 werden die Anrufe direkt an die Serviceabteilung gesendet.
4. Wenn der Kunde in der Leitung bleibt, wird der Anruf an die Zentrale gesendet.

Beschreiben wir die Regeln des Sprachmenüs: 
```
start default action=goto action-target=main

menu name=main playfile=file1
menu name=main button=1 action=redirect action-target=100
menu name=main button=2 action=redirect action-target=101
menu name=main button=3 action=redirect action-target=102
menu name=main default action=redirect action-target=103
```

## Sprachmenü-Konfigurationssyntax

Die Konfiguration hat 3 gültige Tags - start, menu, schedule. Jedes Tag hat seine eigenen zulässigen Attribute, die unten beschrieben werden.
Der Attributname und der Wert werden durch ein Gleichheitszeichen getrennt `attribute=attributeValue`.

Attribute werden durch Leerzeichen getrennt, daher ist es nicht zulässig, Leerzeichen in Attributwerten zu verwenden.

### Tag start 

Erforderliches Tag. Legt die Regeln für das Starten eines eingehenden Anrufs an die TK-Anlage fest.

Es hat die folgenden Attribute:

1. action - Art der Aktion. 

    Es gibt 2 mögliche Werte: 
    * redirect - Weiterleitung an ein Szenario oder eine interne PBX-Nummer;
    * goto - Weiterleitung an Sprachmenü nach seinen Namen.

1. action-target - das Ziel der Aktion. 

    Wenn das Aktionsattribut einen Wert hat:
    * redirect
    
       Sie müssen die interne Telefonnummer oder Szenario-ID angeben.
    
       Szenarien werden in [Ihrem Profile](https://my.zadarma.com/mypbx/in_calls/)erstellt. 
       Die Szenario-ID wird im Format 0-1 festgelegt, wobei 0 die Sprachmenünummer und 1 die Szenarionummer ist.
    
       Szenario kann auch einen Wert blacklist haben - in diesem Fall wird der Anruf als Besetzt abgelehnt.
    
    * goto
    
       Sie müssen den Namen des Sprachmenüs angeben.
        
1. callerid - Filtern nach Anrufernummern, werden durch Kommas getrennt angegeben. 
1. calleddid - Filtern nach angerufenen Telefonnummern, werden durch Kommas getrennt angegeben. 
1. default - Eigenschaft der Standardaktion. Eine der Aktionen muss unbedingt Eigenschaft default haben.
1. schedule - Filtern Sie nach dem Namen des Zeitplans. Sie können ihn verwenden, um die Arbeitszeiten oder Mittagspause anzugeben.

**Beispiel start mit mehreren Filtern:**

```
start default action=redirect action-target=100
start callerid=111 calleddid=112 action=redirect action-target=blacklist 
start callerid=111 action=redirect action-target=101
start calleddid=112 action=redirect action-target=102 
```

* erste Regel - Wenn Nummer 111 die Nummer 112 anruft, wird der Anruf an Szenario blacklist weitergeleitet (der Anruf wird als besetzt abgebrochen);
* Wenn die erste Regel nicht erfüllt wurde, wird die nächste Regel in der Reihenfolge überprüft;
* Wenn keine Regel erfüllt wurde, wird die Standardaktion ausgeführt - Anrufweiterleitung an Nummer 100.


### Tag menu
Legt die Elemente des Sprachmenüs, die Regeln für den Wechsel zwischen Sprachmenüs, die Regeln für die Weiterleitung an Szenarien oder interne PBX-Telefonnummern fest. 

Es hat die gleichen Attribute wie Tag start und auch:

1. name - Der Name des Sprachmenüelements, ein erforderliches Attribut.
1. playfile - ID der wiedergegebenen Datei, ein erforderliches Attribut.

   Es wird einmal für das Sprachmenü in einer von den Filtern getrennten Zeile angegeben und ist für die abgespielte Datei in Sprachmenü verantwortlich. Die ID der hochgeladenen oder vorgelesenen Datei finden Sie in [Ihrem Profile](https://my.zadarma.com/mypbx/in_calls/).
1. timeout - Eingabewartezeit in Sekunden.

   Es wird in derselben Zeile mit Attribute playfile angegeben, der Standardwert ist 3.
1. attempts - Die Anzahl der Wiederholungen der Dateiwiedergabe, wenn der Abonnent nichts gedrückt hat.

   Es wird in derselben Zeile mit Attribute playfile angegeben, der Standardwert ist 1.
1. maxsymbols - Die maximale Zeichenanzahl, die bei Eingabe gewartet werden soll.
   
   Es wird in derselben Zeile mit Attribute playfile angegeben, der Standardwert ist 1.
1. button - Filter zum Drücken einer oder mehrerer Tasten.

### Tag schedule 

1. name - Der Name des Zeitplans.
1. data - Zeitplanbeschreibung.

Zeitplanbeispiel:
```
schedule name=work data=mo,tu,we,th,fr:0800-1300;1400-1700,sa:0900-1500
```
Der Zeitplan hat den Namen work, die festgelegte Zeit ist montags bis freitags von 08:00 bis 12:59:59 Uhr und von 14:00 bis 16:59:59 Uhr, am Samstag von 09:00 bis 14:59:59 Uhr

Gültige Namen der Wochentage: **su, mo, tu, we, th, fr, sa**.

Die Zeit kann für jeden Wochentag in Form eines oder mehrerer durch Semikolons getrennter Intervalle angegeben werden.
Ein Intervall besteht aus zwei durch einen Bindestrich getrennten Werten - der Startzeit und der Endzeit. Die Start- und Endzeiten müssen vierstellig sein.
Die ersten beiden Ziffern stehen für Stunden, die zweiten beiden Ziffern für Minuten.

Es ist auch möglich, die Zeit für mehrere Wochentage gleichzeitig festzulegen, indem die durch Kommas getrennten Wochentage und die durch einen Doppelpunkt getrennte Zeit wie folgt angegeben werden `mo,tu,we,th,fr:0800-1300;1400-1700`.

Wenn die Uhrzeit nicht angegeben ist, wird der gesamte Tag von 00:00:00 bis 23:59:59 Uhr genommen. 
Zum Beispiel: `mo,tu,we,th,fr:0800-1300;1400-1700,sa,su` wo mo,tu,we,th,fr 2 Zeitintervalle von 8:00 bis 12:59:59 und von 14:00:00 bis 16:59:59 und sa,su haben ein Zeitintervall von 00:00:00 bis 23:59:59. 

Zeitplanbeispiel:
```
schedule name=relax data=mo,tu,we,th,fr:1300-1400,sa,su
```
Der Zeitplan hat den Namen relax. Es wird angegeben, dass montags bis freitags die Ruhezeit von 13:00:00 bis 13:59:59 Uhr ist. Samstag und Sonntag sind arbeitsfreie Tage.

### Regeln

Zu den Regeln gehören Konfigurationszeilen, deren Attribute Filter enthalten.

Filter enthalten, wie oben beschrieben, folgende Attribute: callerid, calleddid, button, schedule.
Regeln beschreiben die Bedingungen für die Ausführung bestimmter Aktionen, abhängig von
 Anrufernummer, Angerufene Nummer, Benutzeraktivität und aktuelle Uhrzeit.

**Die Priorität der Regeln wird durch die Reihenfolge der Zeilen bestimmt.**

**Beispielregeln:**

```
...
menu name=main button=1 callerid=111 calleddid=112 schedule=dinner action=redirect action-target=101
menu name=main button=1 callerid=111 action=redirect action-target=102
menu name=main button=1 action=redirect action-target=103
menu name=main default action=redirect action-target=104
...

schedule name=dinner data=mo,tu,we,th,fr:1300-1400
```
* erste Regel - Wenn Nummer 111 zur Mittagszeit die Nummer 112 anruft und die Taste 1 drückt, wird der Anruf an die Nummer 101 weitergeleitet ;
* Wenn die erste Regel nicht erfüllt wurde, wird die nächste Regel in der Reihenfolge überprüft;
* Wenn keine der Regeln erfüllt ist, wird die Standardaktion ausgeführt - Weiterleiten des Anrufs an Nummer 104.

## Ein Beispiel für ein mehrstufiges Sprachmenü:
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
    * Wenn die Nummer 111 oder 222 einen Anruf tätigt, wird der Anruf an Szenario blacklist weitergeleitet (auflegen);
    * sonst, wenn die Bedingung schedule mit dem Namen off-the-clock erfüllt ist, wird der Übergang zum Menü off-the-clock ausgeführt;
    * sonst, wenn die Bedingung schedule mit dem Namen dinner, wird der Übergang zum Menü dinner ausgeführt;
    * sonst, wenn die Nummer 112 oder 223 einen Anruf tätigt, wird der Übergang zum Menü main.1 ausgeführt;
    * sonst wird der Übergang zum Menü main ausgeführt.
1. main menu
    * Datei 43d8a740ec032766 wird abgespielt, Eingabewartezeit - 5,
    Anzahl der Wiederholungen der Dateiwiedergabe, wenn der Abonnent nichts gedrückt hat  - 2, 
     maximale Zeichenanzahl, die bei Eingabe gewartet werden soll - 2.
    * Wenn Taste 1 gedrückt wurde, wird der Übergang zum Menü main.1 ausgeführt;
    * sonst, wenn Taste 2 gedrückt wurde, wird der Übergang zum Menü main.2 ausgeführt;
    * sonst, wenn Taste 3 gedrückt wurde, wird der Übergang an Szenario 0-1 ausgeführt;
    * sonst, wenn Taste 4 gedrückt wurde, wird der Anruf an die interne Nummer 101 geleitet;
    * sonst, wenn Taste 10 gedrückt wurde, wird der Anruf an die interne Nummer 110 geleitet;
    * sonst wird der Anruf an die interne Nummer 100 geleitet.
1. main.1 menu
    * Datei a279dd3a1da19e57 wird abgespielt;
    * Wenn Taste 1 gedrückt wurde, wird der Übergang an Szenario 0-2 ausgeführt;
    * sonst, wenn Taste 2 gedrückt wurde, wird der Anruf an die interne Nummer 102 geleitet;
    * sonst, wenn Taste * gedrückt wurde, wird der Übergang zum Menü main ausgeführt;
    * sonst wird menu.1 wiederholt.
1. main.2 menu
    * Datei a6842305f1996e34 wird abgespielt;
    * Wenn Taste 1 gedrückt wurde, wird der Übergang an Szenario 0-3 ausgeführt;
    * sonst, wenn Taste 2 gedrückt wurde, wird der Anruf an die interne Nummer 103 geleitet;
    * sonst, wenn Taste * gedrückt wurde, wird der Übergang zum Menü main ausgeführt;
    * sonst wird menu.2 wiederholt.
1. dinner menu
    * Datei facdfc7ca2029552 wird abgespielt;
    * Der Anruf wird an Szenario 0-4 weitergeleitet.
1. off-the-clock menu
    * Datei b1b2d11f59d8d208 wird abgespielt;
    * Der Anruf wird an Szenario blacklist weitergeleitet (Anruf wird abgebrochen).
1. dinner schedule
    * Bedingung: Montag - Freitag von 13:00:00 bis 13:59:59;
1. off-the-clock schedule
    * Bedingung: Montag - Freitag von 00:00:00 bis 08:59:59, von 18:00:00 bis 23:59:59, Samstag, Sonntag.