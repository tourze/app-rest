<?php

if ( ! defined('SAE_MYSQL_HOST_M'))
{
    define('SAE_MYSQL_HOST_M', '127.0.0.1');
}
if ( ! defined('SAE_MYSQL_PORT'))
{
    define('SAE_MYSQL_PORT', '3306');
}
if ( ! defined('SAE_MYSQL_DB'))
{
    define('SAE_MYSQL_DB', 'sdk');
}
if ( ! defined('SAE_MYSQL_USER'))
{
    define('SAE_MYSQL_USER', 'root');
}
if ( ! defined('SAE_MYSQL_PASS'))
{
    define('SAE_MYSQL_PASS', 'root');
}

// 本地测试环境
if ($_SERVER['HTTP_HOST'] == 'test.rest.tourze.com')
{
    // 本地调试环境
    $hostname = '127.0.0.1';
    $database = 'com.tourze.rest';
    $username = 'admin';
    $password = 'admin';
}
elseif (IN_SAE)
{
    $hostname = SAE_MYSQL_HOST_M . ':' . SAE_MYSQL_PORT;
    $database = SAE_MYSQL_DB;
    $username = SAE_MYSQL_USER;
    $password = SAE_MYSQL_PASS;
}
else
{
    $hostname = $database = $username = $password = null;
}

return [
    // 默认数据库
    'default'   => [
        'dbname'   => $database,
        'user'     => $username,
        'password' => $password,
        'host'     => $hostname,
        'driver'   => 'pdo_mysql',
    ],

    // rest数据库，暂时是单个数据库
    'rest'      => [
        'dbname'   => $database,
        'user'     => $username,
        'password' => $password,
        'host'     => $hostname,
        'driver'   => 'pdo_mysql',
    ],

    // UC，用户系统数据
    'uc'        => [
        'dbname'   => $database,
        'user'     => $username,
        'password' => $password,
        'host'     => $hostname,
        'driver'   => 'pdo_mysql',
    ],

    /* IGNORE */
    'alternate' => [
        'type'        => 'Pdo',
        'connection'  => [
            /**
             * The following options are available for PDO:
             * string   dsn
             * string   username
             * string   password
             * boolean  persistent
             * string   identifier
             */
            'dsn'        => 'mysql:host=localhost;dbname=sdk',
            'username'   => 'root',
            'password'   => 'r00tdb',
            'persistent' => false,
        ],
        'tablePrefix' => '',
        'charset'     => 'utf8',
        'caching'     => false,
    ],
];

