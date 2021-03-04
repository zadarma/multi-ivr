<?php

use MultiIvr\MultiIvr;

if (isset($_GET['zd_echo'])) {
    exit($_GET['zd_echo']);
}

require_once '../vendor/autoload.php';

$file = <<<TXT
start callerid=111 action=goto action-target=main
start default action=redirect action-target=100

menu name=main playfile=ca17d5b0e3ebaa48
menu name=main button=1 action=goto action-target=main.1
menu name=main button=2 schedule=dinner action=goto action-target=dinner
menu name=main button=2 action=goto action-target=main.1
menu name=main default action=goto action-target=main.2

menu name=main.1 playfile=ac24b48d600825ea
menu name=main.1 default action=redirect action-target=100

menu name=main.2 playfile=ff2265c7537f5392
menu name=main.2 default action=goto action-target=main

menu name=dinner playfile=facdfc7ca2029552
menu name=dinner default action=goto action-target=main

schedule name=dinner data=mo,tu,we,th,fr:1700-1750,su
TXT;

MultiIvr::default()->handle('your key', 'your secret', $file);
