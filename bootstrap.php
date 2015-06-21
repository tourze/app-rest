<?php

use tourze\Base\Base;

if ( ! defined('ROOT_PATH'))
{
    define('ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);
}

require 'vendor/autoload.php';

Base::$cacheDir = ROOT_PATH . 'tmp/cache';
Base::$logConfig = [
    'file' => ROOT_PATH . 'tmp/log/' . date('Y/md') . '.log'
];

if ( ! isset($app))
{
    // SLIM框架实例
    $app = new \rest\Core();
}
