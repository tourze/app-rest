<?php

namespace rest\Storage;

use tourze\Base\Object;

abstract class Base extends Object
{

    /**
     * @var \rest\Core
     */
    public $app;

    /**
     * @var string 资源名称
     */
    public $resourceName;

    public $resourceID;

    public $meta;

    /**
     * @var string
     */
    public $type;

    /**
     * @var bool 是否使用缓存
     */
    public $cache = true;

    /**
     * 获取当前资源请求的字段
     *
     * @return array
     */
    public function getSourceColumns()
    {
        if (isset($this->app->meta['fields']))
        {
            $columns = array_keys($this->app->meta['fields']);
        }
        else
        {
            $columns = [];
        }

        return $columns;
    }
}
