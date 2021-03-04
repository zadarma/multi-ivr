<?php

use MultiIvr\Examples\ConfigRepository;
use MultiIvr\Exceptions\MultiIvrException;
use MultiIvr\Exceptions\ParserException;
use MultiIvr\MultiIvr;

require_once '../../vendor/autoload.php';
require_once 'config-repository.php';
$configTxt = ConfigRepository::getConfig();
$error = null;
if ($_POST) {
    $configTxt = trim((string)$_POST['config']);
    try {
        if (!empty($configTxt)) {
            MultiIvr::default()->validation($configTxt);
        }
        ConfigRepository::saveConfig($configTxt);
    } catch (ParserException $exception) {
        $error = $exception->getMessage();
    } catch (MultiIvrException $exception) {
        $error = $exception->getMessage();
    }
}
?>

<form class="save-form" method="POST">
    <div style="color: red;font-weight: bold;margin-bottom: 20px;">
        <?= $error ?>
    </div>
    <div style="display: flex;">
        <label style="margin-right: 20px;">Config text </label>
        <textarea name="config" rows="30" style="width: 800px"><?= $configTxt ?></textarea>
    </div>
    <input type="submit" value="Save">
</form>
