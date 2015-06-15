<?php

if ( ! defined('ROOT_PATH'))
{
    define('ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);
}

require 'vendor/autoload.php';

if ( ! isset($app))
{
    // SLIM框架实例
    $app = new \rest\Core();
}
