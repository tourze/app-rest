<?php

define('ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require 'vendor/autoload.php';

$loader->addPsr4('', [ROOT_PATH . 'src']);

// SLIM框架实例
$app = new \rest\Core();
