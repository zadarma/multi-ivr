# Interaktywne menu głosowe
Biblioteka PHP do konfiguracji menu głosowego Zadarma

*Zapoznaj się z dokumentacją w innym języku:*
* [Angielski](README.md)
* [Hiszpański](README_ES.md)
* [Niemiecki](README_DE.md)
* [Rosyjski](README_RU.md)
* [Ukraiński](README_UA.md)
* [Francuski](README_FR.md)

## Wymagania:
* PHP >= 7.2.0
* cURL
* TLS v1.2
* php-mbstring
## Konfiguracja
Dodaj zależność `zadarma/multi-ivr` do pliku composer.json twojego projektu:
```
composer require zadarma/multi-ivr
```

## Możliwości

Realizowana możliwość elastycznej konfiguracji menu głosowego za pomocą filtrów i reguł.
Obsługiwane są cztery typy filtrów:
* wg numeru osoby dzwoniącej;
* wg numeru na który dzwonią;
* wg godzin pracy;
* wg zachowania użytkownika.

Reguła składa się z filtrów i wykonanych akcji. Akcja jest wykonywana, jeśli warunki filtrów reguł są zgodne.
Akcje są dwóch rodzajów - przejście do menu głosowego (odtwarzany jest plik dźwiękowy i oczekiwana jest akcja użytkownika) i przekierowanie połączenia na numer telefonu lub scenariusz (dzwonienie na kilka numerów lub czarna lista).

Rozważmy bardziej szczegółowo możliwości poniżej w tekście.
## Korzystania

Aby otrzymywać powiadomienia o połączeniach, musisz utworzyć publicznie dostępne łącze, które będzie akceptować żądania POST z informacjami z systemu Zadarma.
Ten link musi być określony w [panelu klienta](https://my.zadarma.com/api/) w polu "Powiadomienia o połączeniach w centrali telefonicznej".

Link powinien zawierać następujący kod php:
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

`$key` и `$secret` - klucze do autoryzacji w interfejsie API, należy je wygenerować w [panelu klienta](https://my.zadarma.com/api/).
`$ivrMenuConfig` - konfiguracja tekstowa menu głosowego, opiszemy ją bardziej szczegółowo poniżej.

## Przykład konfiguracji

Cel:

Za pomocą menu głosowego podzielić klientów na 3 grupy:

1. Wybierając przycisk 1, przekierowuje połączenia do działu sprzedaży.
2. Wybierając przycisk 2, przekierowuje połączenia do działu zakupów.
3. Wybierając przycisk 3, przekierowuje połączenia do serwisu.
4. Jeśli pozostaniesz na linii, połączenie zostanie przekierowane do sekretarki.


Opisujemy reguły menu głosowego:
```
start default action=goto action-target=main

menu name=main playfile=file1
menu name=main button=1 action=redirect action-target=100
menu name=main button=2 action=redirect action-target=101
menu name=main button=3 action=redirect action-target=102
menu name=main default action=redirect action-target=103
```

## Składnia konfiguracji menu głosowego

Konfiguracja ma 3 prawidłowe tagi - start, menu, schedule. Każdy tag ma własne dozwolone atrybuty, które opisano poniżej.
Nazwa i wartość atrybutu są oddzielone znakiem równości `attribute=attributeValue`.

Atrybuty są oddzielone spacjami, więc używanie spacji w wartościach atrybutów jest niedozwolone.

### Tag start 

Wymagany. Ustawia zasady rozpoczynania połączenia przychodzącego do centrali PBX.

Posiada następujące atrybuty:

1. action - typ akcji. 

    Możliwe 2 wartości: 
    * redirect - przekierowanie na scenariusz lub numer wewnętrzny centrali PBX;
    * goto - przejście do menu głosowego według jego nazwy.

1. action-target - cel akcji. 

    Jeśli atrybut akcji ma wartość:
    * redirect
    
       Musisz podać wewnętrzny numer telefonu lub identyfikator scenariuszu.
    
       Scenariusze możesz utworzyć w [panelu klienta](https://my.zadarma.com/mypbx/in_calls/). 
       Id scenariuszu generowane jest w formacie 0-1, gdzie 0 - numer menu, 1 - numer przycisku.
    
       Scenariusz także może miec wartość blacklist - w takim przypadku połączenie zostanie odrzucone z sygnałem zajętości.
    
    * goto
    
       Musisz określić nazwę menu głosowego.
        
1. callerid - filtr według numerów osób dzwoniących, oddzielonych przecinkami. 
1. calleddid - filtr według numerów, które dzwonią, oddzielonych przecinkami. 
1. default - domyślna oznaczenie akcji. Jedna z czynności musi koniecznie mieć znak default.
1. schedule - filtr z nazwą harmonogramu, za pomocą którego możesz określić godziny pracy lub lunchu.

**Przykład start z kilkoma filtrami:**

```
start default action=redirect action-target=100
start callerid=111 calleddid=112 action=redirect action-target=blacklist 
start callerid=111 action=redirect action-target=101
start calleddid=112 action=redirect action-target=102 
```

* pierwsza reguła - jeśli numer 111 zadzwonił pod numer 112, to połączenie jest przekierowane do scenariuszu blacklist (odrzucić połączenie z sygnałem zajętości)
* jeśli pierwsza reguła nie jest spełniona, sprawdzana jest następna w kolejności;
* jeśli żadna reguła nie została spełniona, wykonywana jest akcja domyślna - przekierowanie połączenia pod numer 100.


### Tag menu
Określa elementy menu głosowego, zasady przełączania się między nimi, zasady przekierowania na scenariusze lub wewnętrzne numery centrali PBX.

Ma te same atrybuty co start, a także:

1. name - nazwa elementu menu głosowego, wymagany atrybut.
1. playfile - id odtwarzanego pliku, wymagany atrybut.

   Jest on określony dla menu głosowego w osobnym wierszu od filtrów; odpowiada za odtwarzany plik menu głosowego. Identyfikator przesłanego lub odczytanego pliku można znaleźć w [panelu klienta](https://my.zadarma.com/mypbx/in_calls/).
1. timeout - czas opóźnienia w celu wyboru przycisku.

   Jest określony w tym samym wierszu, co atrybut playfile, wartość domyślna to - 3.
1. attempts - liczba powtórzeń odtwarzanego pliku, jeśli klient nic nie wybrał.

   Jest określony w jednym wierszu z atrybutem playfile, domyślna wartość to - 1.
1. maxsymbols - maksymalna liczba znaków do oczekiwania na wprowadzenie.
   
  Jest określony w jednym wierszu z atrybutem playfile, domyślna wartość to - 1.

1. button - filtr wybierania jednego lub więcej przycisków.

### Tag schedule 

1. name - nazwa harmonogramu.
1. data - opis harmonogramu.

Przykład:
```
schedule name=work data=mo,tu,we,th,fr:0800-1300;1400-1700,sa:0900-1500
```
Harmonogram ma nazwę work, wskazuje, że godziny pracy są w poniedziałki - piątki od godz 08:00 do 12:59:59 oraz od godz 14:00 do 16:59:59, w sobotę od godz 09:00 do 14:59:59.

Prawidłowe nazwy dni tygodnia: **su, mo, tu, we, th, fr, sa**.

Czas można określić dla każdego dnia tygodnia w postaci jednego lub kilku przedziałów oddzielonych średnikami.
Interwał składa się z dwóch wartości oddzielonych myślnikiem - czasu rozpoczęcia i czasu zakończenia. Czas rozpoczęcia i zakończenia pracy musi składać się z czterech cyfr.
Pierwsze dwie cyfry odpowiadają za godziny, drugie za minuty.

Możliwe jest również ustawienie godzin pracy dla kilku dni tygodnia naraz, określając dni tygodnia oddzielone przecinkami i czas oddzielony dwukropkiem, w ten sposób `mo,tu,we,th,fr:0800-1300;1400-1700`.

Jeśli godziny pracy są nie jest określone, to domyślnie jest cały dzień od 00:00:00 do 23:59:59. 
Np., `mo,tu,we,th,fr:0800-1300;1400-1700,sa,su` mo,tu,we,th,fr mają ustawiony czas pracy od godz 08:00 do 12:59:59 oraz od godz 14:00 do 16:59:59, a dni sa,su od 00:00:00 do 23:59:59. 

Przykład harmonogramu:
```
schedule name=relax data=mo,tu,we,th,fr:1300-1400,sa,su
```
Harmonogram ma nazwę relax, wskazuje, że w poniedziałki - piątki czas obiadu/odpoczynku jest od godz 13:00:00 do 13:59:59, a dni wolne w sobotę i niedzielę.

### Reguły

Reguły obejmują wiersze konfiguracyjne, które mają filtry w swoich atrybutach.

Filtry, jak opisano powyżej, obejmują atrybuty: callerid, calleddid, button, schedule.
Reguły opisują warunki wykonywania określonych czynności w zależności od
 numeru dzwoniącego, numeru na który dzwonią, aktywność użytkownika i aktualny czas.

**Priorytet reguł jest określony przez kolejność wierszy.**

**Przykład reguł:**

```
...
menu name=main button=1 callerid=111 calleddid=112 schedule=dinner action=redirect action-target=101
menu name=main button=1 callerid=111 action=redirect action-target=102
menu name=main button=1 action=redirect action-target=103
menu name=main default action=redirect action-target=104
...

schedule name=dinner data=mo,tu,we,th,fr:1300-1400
```
* pierwsza reguła - jeśli numer 111 zadzwonił pod numer 112 w porze obiadowej i wybrał przycisk 1, to połączenie jest przekierowane pod numer 101;
* jeśli pierwsza reguła nie jest spełniona, sprawdzana jest następna w kolejności;
* jeśli żadna reguła nie została spełniona, wykonywana jest akcja domyślna - przekierowanie połączenia pod numer 100.


## Przykład wielopoziomowego menu głosowego:
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
    * jeśli dzwoni 111 lub 222, to połączenie jest przekierowanie do scenariuszu blacklist (rozłącz się);
    * w przeciwnym razie, wykonywany schedule z nazwą off-the-clock, następnie przejście do menu off-the-clock
    * w przeciwnym razie, wykonywany schedule z nazwą dinner, następnie przejście do menu dinner;
    * jeśli dzwoni 112 lub 223, to połączenie jest przekierowanie do menu main.1;
    * w przeciwnym razie przejście do menu main.
1. menu main
    * Odtwarzanie pliku 43d8a740ec032766, czas opóźnienia wybierania przycisku - 5,
    liczba powtórzeń odtwarzanego pliku, jeśli klient nic nie wybrał  - 2, 
    maksymalna liczba znaków do oczekiwania na wprowadzenie - 2.
    * jeśli wybrano 1, przejście do menu z nazwą main.1;
    * w przeciwnym razie, jeśli wybrano 2, przejście do menu z nazwą main.2;
    * w przeciwnym razie, jeśli wybrano 3, przekierowanie połączenia na scenariusz 0-1;
    * w przeciwnym razie, jeśli wybrano 4, przekierowanie na numer wewnętrzny centrali 101;
    * w przeciwnym razie, jeśli wybrano 10, przekierowanie na numer wewnętrzny centrali 110;
    * w przeciwnym razie, przekierowanie na numer wewnętrzny centrali 100.
1. main.1 menu
    * Odtwarzanie pliku a279dd3a1da19e57;
    * jeśli wybrano 1, przekierowanie połączenia na scenariusz 0-2;
    * w przeciwnym razie, jeśli wybrano 2, przekierowanie na numer wewnętrzny centrali 102;
    * w przeciwnym razie, jeśli wybrano *, przekierowanie na menu main;
    * w przeciwnym razie, powtórzenie menu.1.
1. main.2 menu
    * Odtwarzanie pliku a6842305f1996e34;
    * jeśli wybrano 1, przekierowanie połączenia na scenariusz 0-3;
    * w przeciwnym razie, jeśli wybrano 2, przekierowanie na numer wewnętrzny centrali 103;
    * w przeciwnym razie, jeśli wybrano *, przekierowanie na menu main;
    * w przeciwnym razie, powtórzenie menu.2.
1. dinner menu
    * Odtwarzanie pliku facdfc7ca2029552;
    * przekierowanie połączenia na scenariusz 0-4.
1. off-the-clock menu
    * Odtwarzanie pliku b1b2d11f59d8d208;
    * вprzekierowanie połączenia na scenariusz blacklist(rozłącz się).
1. dinner schedule
    * Reguła: pon - pt od godz 13:00:00 do 13:59:59;
1. off-the-clock schedule
    * Reguła: pon - pt od godz 00:00:00 do 08:59:59,18:00:00 do 23:59:59, sobota, niedziela.