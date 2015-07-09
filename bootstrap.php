<?php

use tourze\Base\Base;
use tourze\Base\Debug;
use tourze\Route\Route;

defined('ROOT_PATH') || define('ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);
defined('RESOURCE_PATH') || define('RESOURCE_PATH', ROOT_PATH . 'resource' . DIRECTORY_SEPARATOR);
defined('STORAGE_PATH') || define('STORAGE_PATH', ROOT_PATH . 'storage' . DIRECTORY_SEPARATOR);

require 'vendor/autoload.php';

Base::$cacheDir = ROOT_PATH . 'tmp/cache';
Base::$logConfig = [
    'file' => ROOT_PATH . 'tmp/log/' . date('Y/md') . '.log'
];

// 指定控制器命名空间
Route::$defaultNamespace = '\rest\Controller\\';

Debug::enable();

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
