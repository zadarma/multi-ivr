<?php

use MultiIvr\Examples\ConfigRepository;
use MultiIvr\MultiIvr;

if (isset($_GET['zd_echo'])) {
    exit($_GET['zd_echo']);
}

require_once '../../vendor/autoload.php';
require_once 'config-repository.php';

$configTxt = ConfigRepository::getConfig();
[$key, $secret] = ConfigRepository::getKeys();
MultiIvr::default()->handle($key, $secret, $configTxt);
