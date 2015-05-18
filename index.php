<?php

require 'bootstrap.php';

/**
 * 一个请求可以分成几个步骤：
 *
 * 1. 解析请求路径，并获取meta数据
 * 2. 根据meta数据，获取对应的storage
 * 3. 其他处理
 */

$app->map('/:resource', function ($resource) use ($app)
{
    // 加载资源
    $app->loadResource($resource);
    $app->dispatchMethod();
})->conditions(['resource' => '(.*)'])->via('GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD');

$app->run();
