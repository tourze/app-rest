<?php

namespace rest;
use tourze\Base\Object;

/**
 * 过滤器处理
 *
 * @package rest
 */
class Filter extends Object
{

    /**
     * @var Core
     */
    public $rest;

    /**
     * @param array $config
     * @return \rest\Filter
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
        'date_format'     => '\rest\Filter\Date\Format',
    ];

    public function prepareMethodDispatch()
    {
    }
}
