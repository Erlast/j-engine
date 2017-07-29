<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('ROOT', dirname(__FILE__));
define('APP_ROOT',ROOT."/../application");

if (!require_once(APP_ROOT . "/Boot.php")) {
    die ('BOOT ERROR');
}
$config = require(APP_ROOT. '/config/main.php');

\components\App::instance($config)->run();

