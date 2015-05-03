<?php

use sdk\Base\Debug;
use sdk\Base\Flow;
use sdk\Base\Sdk;
use sdk\Http\HttpRequest;
use sdk\Mvc\Route;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require 'vendor/autoload.php';

// php文件后缀
defined('EXT') || define('EXT', '.php');

// 路径分隔符
defined('DS') || define('DS', DIRECTORY_SEPARATOR);

// 判断是否在sae中
defined('IN_SAE') || define('IN_SAE', function_exists('sae_debug'));

// 设置PHP错误级别，根据自己的需要来设置
error_reporting(E_ALL | E_STRICT);

define('DOCROOT', __DIR__ . DIRECTORY_SEPARATOR);
define('APPPATH', __DIR__ . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);

Debug::enable();

/**
 * 自动加载main.php并进行初始化
 */
$mainConfig = [];
foreach ([APPPATH.'main'.EXT, APPPATH.'main-local'.EXT] as $mainFile)
{
    if (is_file($mainFile))
    {
        $mainConfig = array_merge($mainConfig, @include $mainFile);
    }
}
foreach ($mainConfig as $k => $v)
{
    Sdk::$$k = $v;
}

unset($mainConfig, $k, $v);

if (
    Sdk::$profileEntry
    && isset($_GET[Sdk::$profileEntry])
    && Sdk::$profilePassword
    && $_GET[Sdk::$profileEntry] == Sdk::$profilePassword)
{
    if (function_exists('sae_xhprof_start'))
    {
        sae_xhprof_start();
    }
}

/**
 * SDK启动
 */
$sdk = Sdk::instance();

if ( ! defined('REST_TABLE_PREFIX'))
{
    define('REST_TABLE_PREFIX', 'rest_');
}

if ( ! defined('REST_DB_GROUP'))
{
    define('REST_DB_GROUP', 'rest');
}

if ( ! defined('REST_PATH'))
{
    define('REST_PATH', '');
}

Route::set('rest', REST_PATH.'<slug>(.<ext>)', [
    'slug' => '.*',
    'ext'  => 'json|xml|csv',
])
    ->filter(
    // 使用filter来进行处理，因为正则处理的话太复杂了
        function (Route $route, $params, HttpRequest $request)
        {
            // 针对slug进行判断
            if (isset($params['slug']) && $params['slug'])
            {
                $slug = $params['slug'];
                $slug = explode('/', $slug);

                // 如果最后一位是数字，那么就当做为ID
                if (is_numeric($slug[count($slug) - 1]))
                {
                    $params['id'] = array_pop($slug);
                }
                // 最后一位是_count，那么就是个计数器
                if ($slug[count($slug) - 1] == '_count')
                {
                    $params['id'] = array_pop($slug);
                }
                $params['action'] = implode('/', $slug);
                unset($params['slug']);
            }
            return $params;
        }
    )
    ->defaults([
        'controller' => 'Rest',
    ]);

// 主工作流
$flow = Flow::instance('sdk');
$flow->contexts = [
    'globals' => $GLOBALS,
    'sdk'     => $sdk,
];
$flow->layers = Sdk::$layers;
$flow->start();

unset($sdk);

if (
    Sdk::$profileEntry
    && isset($_GET[Sdk::$profileEntry])
    && Sdk::$profilePassword
    && $_GET[Sdk::$profileEntry] == Sdk::$profilePassword)
{
    if (function_exists('sae_xhprof_end'))
    {
        sae_xhprof_end();
    }
}
