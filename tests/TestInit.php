<?php
$autoloader = dirname(__DIR__) . '/vendor/autoload.php';
if (!file_exists($autoloader)) {
    echo "Please `./composer.phar install` first.";
    exit(1);
}
require_once $autoloader;
