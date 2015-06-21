<?php

use tourze\Base\Base;
use tourze\Base\Flow;

require '../bootstrap.php';

/**
 * SDK启动
 */
$app = Base::instance();

// 主工作流
$flow = Flow::instance('sdk');
$flow->contexts = [
    'app'     => $app,
];
$flow->layers = Base::$layers;
$flow->start();
