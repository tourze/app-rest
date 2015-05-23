<?php

if ( ! defined('ROOT_PATH'))
{
    define('ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);
}

if ( ! isset($app))
{
    /** @var \Composer\Autoload\ClassLoader $loader */
    $loader = require 'vendor/autoload.php';
    $loader->addPsr4('', [ROOT_PATH . 'src']);

    // SLIM框架实例
    $app = new \rest\Core();
}
