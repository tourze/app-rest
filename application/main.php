<?php

/**
 * 项目基础配置数组
 */

return [

    // 当前应用的路径
    'baseUrl' => '/',

    // 是否显示缺省文件，如果已经开启伪静态，可以设为false
    'indexFile' => false,

    'cacheActive' => true,

    'cacheDir'  => APPPATH.'tmp/cache',

    'cacheLife' => 300,

    'logConfig' => [
        'file' => APPPATH.'tmp/log/'.date('Y/md').'.log'
    ],

    // 是否显示错误调试信息
    'errors'    => true,

    'layers' => [
        'sdk\Base\SdkFlow',  // SDK基础工作层
        'sdk\Http\HttpFlow', // 执行HTTP相关控制
        'sdk\Mvc\MvcFlow',   // MVC执行
    ],
];
