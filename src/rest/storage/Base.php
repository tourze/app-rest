<?php

namespace rest\storage;

abstract class Base
{

    /**
     * @var \rest\Core
     */
    public $app;

    public $resourceName;

    public $resourceID;

    public $meta;

    public function __construct($params)
    {
        foreach ($params as $k => $v)
        {
            $this->$k = $v;
        }
    }

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
