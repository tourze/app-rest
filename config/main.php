<?php

return [

    'component' => [
        'http'    => [
            'class'  => 'tourze\Server\Component\Http',
            'params' => [
            ],
            'call'   => [
            ],
        ],
        'session' => [
            'class'  => 'tourze\Server\Component\Session',
            'params' => [
            ],
            'call'   => [
            ],
        ],
        'log'     => [
            'class'  => 'tourze\Server\Component\Log',
            'params' => [
            ],
            'call'   => [
            ],
        ],
    ],
    'server'    => [
        // 默认的web配置
        'rest-web' => [
            'count'          => 4, // 打开进程数
            'user'           => '', // 使用什么用户打开
            'reloadable'     => true, // 是否支持平滑重启
            'socketName'     => 'http://0.0.0.0:8011', // 默认监听8080端口
            'contextOptions' => [], // 上下文选项
            'siteList'       => [
                'www.example.com' => realpath(__DIR__ . '/../web'),
            ],
            'rewrite'        => 'index.php',
            //'initClass'      => '',
        ],
    ],

];
