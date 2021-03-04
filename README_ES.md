# Menú de voz interactivo
Biblioteca PHP para la configuración del menú de voz Zadarma

*Lee la descripción en otros idiomas:*
* [Inglés](README.md)
* [Alemán](README_DE.md)
* [Polaco](README_PL.md)
* [Ruso](README_RU.md)
* [Ucraniano](README_UA.md)
* [francés](README_FR.md)

## Requisitos:
* PHP >= 7.2.0
* cURL
* TLS v1.2
* php-mbstring
## Configuración
Agrega la dependencia `zadarma/multi-ivr` en el archivo composer.json de tu proyecto:
```
composer require zadarma/multi-ivr
```

## Posibilidades

Se ha implementado la posibilidad de configurar de forma flexible el menú de voz con filtros y reglas.
Soporte de cuatro tipos de filtros:
* por número del llamante;
* por número al que se ha llamado;
* filtro por horario;
* acción del usuario.

La regla consiste de filtros y acciones. La acción se realizará en caso de coincidencia de condiciones de filtros de regla.
La acción puede ser de dos tipos: ir al menú (se reproduce el archivo de voz y se espera la acción del usuario) y transferencia de llamada al número de teléfono o escenario (llamadas a varios números o blacklist).

Vamos a ver las posibilidades con más detalle más abajo según el texto.
## Uso

Para recibir las notificaciones sobre llamadas es necesario crear un enlace abierto al público que recibirá las solicitudes POST con la información del sistema de Zadarma.
Este enlace se debe indicar en el [área personal](https://my.zadarma.com/api/) bajo el encabezado “Notificaciones de las llamadas en la centralita".

En el enlace se requiere colocar el siguiente código php:
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

`$key` y `$secret` - claves para la autorización en la interfaz API, se obtienen en el [área personal](https://my.zadarma.com/api/).
`$ivrMenuConfig` - configuración de texto del menú de voz, hablaremos más sobre él a continuación.

## Ejemplo de configuración

Tarea:
Con la ayuda del menú de voz separar a clientes en 2 grupos:

1. Al pulsar el botón 1 enviar las llamadas al departamento de ventas.
2. Al pulsar el botón 2 enviar las llamadas al departamento de ventas.
3. Al pulsar el botón 3 enviar las llamadas al departamento de atención al cliente.
4. Si se permanece en línea la llamada se envía a recepción.

Describimos las reglas del menú de voz:
```
start default action=goto action-target=main

menu name=main playfile=file1
menu name=main button=1 action=redirect action-target=100
menu name=main button=2 action=redirect action-target=101
menu name=main button=3 action=redirect action-target=102
menu name=main default action=redirect action-target=103
```

## Sintaxis de configuración del menú de voz

La configuración contiene 3 etiquetas válidas - start, menu, schedule. Cada etiqueta contiene sus atributos permitidos que se describen más abajo.
El nombre y el valor del atributo se separan por el signo igual `attribute=attributeValue`.

Los atributos se separan por espacios, por ello no se permite usar espacios en el valor de los atributos.

### Тег start 

Obligatorio. Establece las reglas del inicio de la llamada entrante a la centralita.

Contiene los siguientes atributos:

1. action - tipo de acción. 

    2 valores posibles: 
    * redirect - transferencia al escenario o extensión de la centralita;
    * goto - traspaso al menú de voz por su nombre.

1. action-target - objetivo de la acción. 

    Si el atributo action tiene valor:
    * redirect
    
       Se requiere indicar la extensión o id del escenario.
    
       Los escenarios se crean en el [área personal](https://my.zadarma.com/mypbx/in_calls/). 
       Id del escenario se establece en formato 0-1 donde 0 - número del menú de voz, 1 - número del escenario.
    
       El escenario también tiene el valor blacklist - en este caso la llamada será rechazada con el tono de ocupadoо.
    
    * goto
    
       Se requiere indicar el nombre del menú de voz.
        
1. callerid - filtro por números de llamantes, se indican separados por comas. 
1. calleddid - filtro por números a los que se llama, фильтр по номерам, se indican separados por comas.  
1. default - signo de acción predeterminado. Una de las acciones debe tener necesariamente el signo default.
1. schedule - filtro con el nombre del horario, a través de éste se puede indicar horario laboral o de comida.

**Ejemplo start con algunos filtros:**

```
start default action=redirect action-target=100
start callerid=111 calleddid=112 action=redirect action-target=blacklist 
start callerid=111 action=redirect action-target=101
start calleddid=112 action=redirect action-target=102 
```

* primera regla - si ha llamado el número 111 al número 112 se cumple la transferencia de llamada al escenario blacklist (colgar la llamada con tono de ocupado)
* si la primera regla no se cumple se comprueba el resto por orden;
* si no se cumple una de las reglas se cumple la acción predeterminada.


### Etiqueta menu
Establece elementos del menú de voz, las reglas de transferencias entre éstos, reglas de la transferencia al escenario o extensiones de la centralita.

Contiene los mismos atributos que start y también:

1. name - nombre del elemento del menú de voz, atributo obligatorio.
1. playfile - id del archivo a reproducir, atributo obligatorio.

   Se indica una vez para el menú de voz en una línea separada de los filtros, responde por el archivo a reproducir del menú de voz. Se puede conocer la id del archivo cargado o texto insertado en el [área personal](https://my.zadarma.com/mypbx/in_calls/).
1. timeout - tiempo de espera de marcado en segundos.

   Se indica en la misma línea con el atributo playfile, valor por defecto - 3.
1. attempts - cantidad de repeticiones del archivo a reproducir si el cliente no ha pulsado nada.

   Se indica en la misma línea con el atributo playfile, valor por defecto - 1
1. maxsymbols - cantidad máxima de caracteres que se espera marcar.
   
   Se indica en la misma línea con el atributo playfile, valor por defecto - 1.
1. button - filtro de pulsar uno o varios botones.

### Etiqueta schedule 

1. name - nombre del horario.
1. data - descripción del horario.

Ejemplo del horario:
```
schedule name=work data=mo,tu,we,th,fr:0800-1300;1400-1700,sa:0900-1500
```
El horario tiene el nombre de work, indica que de lunes a viernes el horario es de 08:00 a 12:59:59 y de 14:00 a 16:59:59, los sábados de 09:00 a 14:59:59.

Nombres permitidos para los días de la semana: **su, mo, tu, we, th, fr, sa**.

El horario se puede indicar para cada día de la semana en forma de uno o varios intervalos separados por punto y coma. 
El intervalo consiste de dos valores separados por guión medio - hora inicio y hora fin. La hora inicio y fin deben consistir de 4 dígitos. Los primeros dos responden por las horas, las siguientes por minutos.

También se puede indicar el horario para varios días de la semana a la vez indicando los días de la semana a través de comas y la hora a través de dos puntos, de esta forma `mo,tu,we,th,fr:0800-1300;1400-1700`.

Si la hora no se ha indicado se selecciona todo el día desde 00:00:00 hasta 23:59:59. 
Por ejemplo, `mo,tu,we,th,fr:0800-1300;1400-1700,sa,su` mo,tu,we,th,fr contienen 2 intervalos de tiempo de 8:00 a 12:59:59 y de 14:00:00 a 16:59:59 y sa,su contienen un intervalo de tiempo de 00:00:00 a 23:59:59.

Ejemplo de horario:
```
schedule name=relax data=mo,tu,we,th,fr:1300-1400,sa,su
```
El horario contiene el nombre de relax, indica que de lunes a viernes el horario de descanso es de 13:00:00 a 13:59:59, los sábados y domingos descanso.

### Reglas

Las reglas incluyen líneas de configuración que contienen filtros en sus atributos.

A los filtros, tal y como se ha descrito anteriormente, corresponden los atributos: callerid, calleddid, button, schedule.
Las reglas describen las condiciones de cumplimiento de determinadas acciones dependiendo del número del llamante, números a los que se llama, acciones del usuario y hora actual.

**La prioridad de las reglas se determina por orden de las líneas.**

**Ejemplo de reglas:**

```
...
menu name=main button=1 callerid=111 calleddid=112 schedule=dinner action=redirect action-target=101
menu name=main button=1 callerid=111 action=redirect action-target=102
menu name=main button=1 action=redirect action-target=103
menu name=main default action=redirect action-target=104
...

schedule name=dinner data=mo,tu,we,th,fr:1300-1400
```
* primera regla - si ha llamado el número 111 al número 112, ha pulsado el botón 1 en horario de comida se cumple la transferencia de llamada al número 101;
* si la primera regla no se cumple se comprueban las siguientes por orden;
* si ninguna de las reglas se cumple se realiza la acción por defecto - transferencia de llamada al número 104.

## Ejemplo del menú de voz multinivel:
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
    * si llama el número 111 o 222 se cumple la transferencia de llamada al escenario blacklist(colgar la llamada);
    * de lo contrario si se cumple la condición schedule con el nombre off-the-clock se realiza la transferencia al menú off-the-clock
    * de lo contrario si se cumple la condición schedule con el nombre dinner se realiza la transferencia al menú dinner;
    * de lo contrario si llama el número 112 o 223 se realiza la transferencia al menú main.1;
    * de lo contrario se realiza la transferencia al menú main.
1. main menu
    * Archivo a reproducir 43d8a740ec032766, tiempo de espera de marcado - 5,
    Cantidad de repeticiones del archivo a reproducir si el llamante no ha pulsado nada - 2, 
    Cantidad máxima de caracteres de marcado a esperar - 2.
    * si se pulsa 1 se realiza la transferencia al menú main.1;
    * de lo contrario si se pulsa 2 se realiza la transferencia al menú main.2;
    * de lo contrario si se pulsa 3 se realiza la transferencia de llamada al escenario 0-1;
    * de lo contrario si se pulsa 4 se realiza la transferencia a la extensión 101 de la centralita;
    * de lo contrario si se pulsa 10 se realiza la transferencia de llamada a la extensión 110 de la centralita;
    * de lo contrario se realiza la transferencia de la llamada a la extensión 100 de la centralita.
1. main.1 menu
    * Reproducimos el archivo a279dd3a1da19e57;
    * si se pulsa 1 se realiza la transferencia de la llamada al escenario 0-2;
    * de lo contrario si se pulsa 2 se realiza la transferencia de llamada a la extensión 102 de la centralita;
    * de lo contrario si se pulsa * se realiza la transferencia al menú main;
    * de lo contrario se repite el menu.1.
1. main.2 menu
    * Reproducimos el archivo a6842305f1996e34;
    * si se pulsa 1 se realiza la transferencia de llamada al escenario 0-3;
    * de lo contrario si se pulsa 2 se realiza la transferencia de llamada a la extensión 103 de la centralita;
    * de lo contrario si se pulsa * se realiza la transferencia al menú;
    * de lo contrario repetimos el menu.2.
1. dinner menu
    * Reproducimos el archivo facdfc7ca2029552;
    * se realiza la transferencia de llamada al escenario 0-4.
1. off-the-clock menu
    * Reproducimos el archivo b1b2d11f59d8d208;
    * se realiza la transferencia de llamada al escenario blacklist(colgar la llamada).
1. dinner schedule
    * Condición: lunes - viernes de 13:00:00 a 13:59:59;
1. off-the-clock schedule
    * Condición: lunes - viernes dс 00:00:00 a 08:59:59,18:00:00 a 23:59:59, sábado, domingo.