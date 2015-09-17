<?php

namespace rest;
use tourze\Base\Object;

/**
 * 逻辑处理
 *
 * @package rest
 */
class Logic extends Object
{

    /**
     * @var Core
     */
    public $rest;

    /**
     * @param array $config
     * @return \rest\Logic
     */
    public static function instance($config = [])
    {
        //var_dump($config);die();
        return new self($config);
    }

    /**
     * @var array 逻辑处理类名映射表
     */
    public $classMap = [
        'date_format'     => '\rest\Logic\Date\Format',
        'result_not_null' => '\rest\Logic\ResultNotNull',
    ];

    public function prepareMethodDispatch()
    {
    }
}
