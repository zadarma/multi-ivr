# Menu vocal interactif (SVI)
PHP bibliothèque pour le réglage du SVI Zadarma

*Lire la description en d’autres langues:*
* [Anglais](README.md)
* [Espagnol](README_ES.md)
* [Allemand](README_DE.md)
* [Polonais](README_PL.md)
* [Russe](README_RU.md)
* [Ukrainien](README_UA.md)

## Prescriptions :
* PHP >= 7.2.0
* cURL
* TLS v1.2
* php-mbstring
## Installation
Ajoutez un lien `zadarma/multi-ivr` dans une fichier composer.json de votre projet:
```
composer require zadarma/multi-ivr
```

## Possibilités

La configuration flexible du SVI est réalisée grâce aux filtres et règles.
Quatre types de filtres sont gérés:
* par numéro de l’abonné;
* par numéro que vous appelez;
* filtre d’horaire;
* action de l’utilisateur.

La règle contient des filtres et des actions. L’action s’effectue en cas de coïncidence des conditions des filtres des règles.
Les actions sont de deux types - passage au menu (le fichier audio est reproduit et l’action de l’utilisateur est attendu) et le transfert d’appel au numéro ou scénario (appel vers plusieurs numéros ou blacklist).

Nous allons voir des possibilités plus en détail.
## Utilisation

Pour recevoir des notifications sur les appels il faut créer le lien public, qui reçoit des demandes POST avec l’information du système Zadarma.
Ce lien doit être indiqué dans [l’espace client](https://my.zadarma.com/api/) avec le titre "Notifications sur les appels dans le standard virtuel".

Il faut placer le code php suivant sur le lien:
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

`$key` et `$secret` - clés d’autorisation dans l’interface API, il faut les recevoir dans [l’espace client](https://my.zadarma.com/api/).
`$ivrMenuConfig` - configuration de texte du SVI, nous allons en parler plus en détail.

## Exemple de configuration 

Tâche:

A l’aide du SVI diviser les clients en 3 groupes:

1. En appuyant sur le bouton 1 envoyer les appels au département de vente.
2. En appuyant sur le bouton 2 envoyer les appels au département d’achat.
3. En appuyant sur le bouton 3 envoyer les appels au département de service.
4. Si le client attend toujours, l’appel sera renvoyé vers le secrétaire.

Décrivons les règles du SVI:
```
start default action=goto action-target=main

menu name=main playfile=file1
menu name=main button=1 action=redirect action-target=100
menu name=main button=2 action=redirect action-target=101
menu name=main button=3 action=redirect action-target=102
menu name=main default action=redirect action-target=103
```

## Syntax de configuration du SVI

La configuration a trois tags possibles - start, menu, schedule. Chaque tag a ses propres attributs, décrits ci-dessous.
Le nom et la notion de l’attribut sont divisés par le signe « égal » `attribute=attributeValue`.

Les attributs sont divisés par des espaces, c’est pourquoi il ne faut pas utiliser des espaces dans les notions des attributs.

### Tag start 

Obligatoire. Indique une règle de l’appel entrant dans le standard virtuel.

Il a des attributs suivants:

1. action - type d’action. 

    2 notions possibles: 
    * redirect - renvoi vers le scénario ou numéro interne du standard virtuel;
    * goto - passage au menu vocal par son nom.

1. action-target - bit d’action. 

    Si l’attribut action a une notion:
    * redirect
    
       Il faut indiquer le numéro interne ou l’id du scénario.
    
       Les scénarios sont créés dans [l’espace client](https://my.zadarma.com/mypbx/in_calls/). 
       Id du scénario est indiqué dans un format 0-1, où 0 - un numéro du SVI, 1 - numéro du scénario.
    
       Le scénario peut avoir la notion blacklist - dans ce cas l’appel sera  rejeté avec un signal occupé.
    
    * goto
    
       Il faut indiquer le nom du SVI.
        
1. callerid - filtre par numéros de ceux qui appellent, séparés par une virgule. 
1. calleddid - filtre par numéros qu’on appelle, séparés par une virgule. 
1. default - indice de l’action par défaut. Une des actions doit avoir l’indice default.
1. schedule - filtre avec le nom d’horaire, où on peut indiquer l’heure du travail et du déjeuner.

**Exemple start avec quelques filtres:**

```
start default action=redirect action-target=100
start callerid=111 calleddid=112 action=redirect action-target=blacklist 
start callerid=111 action=redirect action-target=101
start calleddid=112 action=redirect action-target=102 
```

* première règle - si le numéro 111 appelle vers le numéro 112, la règle de renvoi de l’appel vers le scénario blacklist a fonctionné (rejeter avec le signal occupé)
* si la première règle n’a pas fonctionné, on passe à la suivante;
* si aucune règle n’a fonctionné, l’action par défaut fonctionne - le transfert de l’appel vers le numéro 100.


### Tag menu
Indique les éléments du SVI, les règles de passage entre eux, les règles de transfert vers les scénarios ou numéros internes du standard virtuel. 

Il a des mêmes attributs que start, et aussi:

1. name - nom d’élément du SVI, l'attribut obligatoire.
1. playfile - id du fichier reproduit, l'attribut obligatoire.

   Il est indiqué une fois pour le menu vocal dans une ligne distincte, est responsable du fichier reproduit dans le SVI. id du fichier chargé ou lu vous pouvez voir dans [l’espace client](https://my.zadarma.com/mypbx/in_calls/).
1. timeout - durée d’attente de saisie en secondes.

   Indiqué dans une ligne avec l'attribut playfile, notion par défaut - 3.
1. attempts - nombre de répétitions du fichier reproduit si l’abonné n’a rien appuyé.

   Indiqué dans une ligne avec l’attribut playfile, notion par défaut - 1.
1. maxsymbols - nombre maximal de symboles dont la saisie il faut attendre.
   
   Indiqué dans une ligne avec l’attribut playfile, notion par défaut - 1.
1. button - filtre d’appui d’un ou plusieurs boutons.

### Tag schedule 

1. name - nom d’horaire.
1. data - description d'horaire.

Exemple d’horaire:
```
schedule name=work data=mo,tu,we,th,fr:0800-1300;1400-1700,sa:0900-1500
```
L’horaire a le nom work, il est indiqué, lundi - vendredi de 08:00 à 12:59:59 et de 14:00 à 16:59:59, samedi de 09:00 à 14:59:59.

Nom possibles des jours de la semaine: **su, mo, tu, we, th, fr, sa**.

L’heure peut être indiquée pour chaque jour de la semaine, une ou plusieurs intervalles, séparées par un point - virgule.
L’intervalle contient deux notions séparées par un trait d’union - l’heure du début et l’heure de la fin. L’heure du début et de la fin doivent contenir quatre chiffres.
Les deux premiers chiffres indiquent l’heure, les deuxièmes  - les minutes.

On peut également indiquer l’heure pour quelques jours de la semaine, en indiquant les jours de la semaine séparés par une virgule, et les heures séparées par les deux points, par exemple `mo,tu,we,th,fr:0800-1300;1400-1700`.

Si l’heure n’est pas indiquée, la journée en entier est considérée de 00:00:00 à 23:59:59. 
Par exemple, `mo,tu,we,th,fr:0800-1300;1400-1700,sa,su` mo,tu,we,th,fr ont 2 intervalles de 8:00 à 12:59:59 et de 14:00:00 à 16:59:59, et sa,su ont une intervalle de 00:00:00 à 23:59:59.

Exemple d’horaire:
```
schedule name=relax data=mo,tu,we,th,fr:1300-1400,sa,su
```
L’horaire a le nom relax, il est indiqué que lundi - vendredi l’heure de repos de 13:00:00 à 13:59:59, samedi et dimanche sont les jours de repos.

### Règles

Les règles sont les lignes de configuration qui ont les filtres dans leurs attributs.

Les filtres contiennent les attributs suivants: callerid, calleddid, button, schedule.
Les règles qui décrivent les conditions des actions selon
 le numéro de celui qui appelle, le numéro qu’on appelle, les actions de l'utilisateur et l’heure actuelle.

**La priorité est définie selon l’ordre des lignes.**

**Exemple des règles:**

```
...
menu name=main button=1 callerid=111 calleddid=112 schedule=dinner action=redirect action-target=101
menu name=main button=1 callerid=111 action=redirect action-target=102
menu name=main button=1 action=redirect action-target=103
menu name=main default action=redirect action-target=104
...

schedule name=dinner data=mo,tu,we,th,fr:1300-1400
```
* première règle - si le numéro 111 appelle vers le numéro 112, appuie sur le bouton 1 pendant l’heure du déjeuner, le transfert de l’appel vers le numéro 101 s’effectue;
* si la première règle n’a pas fonctionné, la règle suivante s’effectue;
* si aucune règle n’a fonctionné, l’action par défaut fonctionne - le transfert de l’appel vers le numéro 104.

## Exemple du menu vocal à plusieurs couches:
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
    * si le numéro 111 ou 222 appelle, le transfert d’appel au scénario blacklist s’effectue (rejeter);
    * sinon si la condition schedule avec le nom off-the-clock a fonctionné, le passage au menu off-the-clock s’effectue;
    * sinon si la condition schedule avec le nom dinner a fonctionné, le passage au menu dinner s’effectue;
    * sinon si le numéro 112 ou 223 appelle, le passage au menu main.1 s’effectue;
    * sinon le passage au menu main s’effectue.
1. main menu
    * Reproduisons le fichier 43d8a740ec032766, la durée d’attente de saisie - 5,
    nombre de répétitions du fichier reproduit, si l’abonné n’a rien appuyé  - 2, 
    nombre maximal de symboles dont la saisie il faut attendre - 2.
    * si on appuie 1, le passage au menu main.1 s'effectue;
    * si on appuie 2, le passage au menu main.2 s’effectue;
    * si on appuie 3, le transfert d’appel vers le scénario 0-1 s’effectue;
    * sinon si on appuie 4, le transfert d’appel vers le numéro interne 101 du standard virtuel s’effectue;
    * sinon si on appuie 10, le transfert d’appel vers le numéro interne 110 du standard virtuel s’effectue;
    * sinon le transfert d’appel vers le numéro interne 100 du standard virtuel s’effectue.
1. main.1 menu
    * Reproduisons le fichier a279dd3a1da19e57;
    * si on appuie 1, le transfert d’appel vers le scénario 0-2 s’effectue;
    * sinon si on appuie 2, le transfert d’appel vers le numéro interne 102 du standard virtuel s’effectue;
    * sinon si on appuie *, le passage au menu main s’effectue;
    * sinon on répète menu.1.
1. main.2 menu
    * Reproduisons le fichier a6842305f1996e34;
    * si on appuie 1, le transfert d’appel vers le scénario 0-3 s’effectue;
    * sinon si on appuie 2, le transfert d’appel vers le numéro interne 103 du standard virtuel s’effectue;
    * sinon si on appuie *, le passage au menu main s’effectue;
    * sinon on répète menu.2.
1. dinner menu
    * Reproduisons le fichier facdfc7ca2029552;
    * le transfert d’appel vers le scénario 0-4 s’effectue.
1. off-the-clock menu
    * Reproduisons le fichier b1b2d11f59d8d208;
    * le transfert d’appel vers le scénario blacklist(rejeter) s’effectue.
1. dinner schedule
    * Condition: lundi - vendredi de 13:00:00 à 13:59:59;
1. off-the-clock schedule
    * Condition: lundi - vendredi de 00:00:00 à 08:59:59, de 18:00:00 à 23:59:59, samedi, dimanche.