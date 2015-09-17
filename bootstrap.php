<?php

use tourze\Base\Config;
use tourze\Route\Route;

if (is_file(__DIR__ . '/vendor/autoload.php'))
{
    require_once __DIR__ . '/vendor/autoload.php';
}

if ( ! defined('ROOT_PATH'))
{
    define('ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);
}

if ( ! defined('RESOURCE_PATH'))
{
    define('RESOURCE_PATH', ROOT_PATH . 'resource' . DIRECTORY_SEPARATOR);
}

if ( ! defined('STORAGE_PATH'))
{
    define('STORAGE_PATH', ROOT_PATH . 'storage' . DIRECTORY_SEPARATOR);
}

// 指定控制器命名空间
Route::$defaultNamespace = '\rest\Controller\\';

Config::addPath(ROOT_PATH . 'config' . DIRECTORY_SEPARATOR);

/**
 * 一个请求可以分成几个步骤：
 *
 * 1. 解析请求路径，并获取meta数据
 * 2. 根据meta数据，获取对应的storage
 * 3. 其他处理
 */
Route::set('rest', '<resource>', ['resource' => '.*'])
    ->defaults([
        'controller' => 'Rest',
        'action'     => 'index',
    ]);
