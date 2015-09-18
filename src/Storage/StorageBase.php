<?php

namespace rest\Storage;

use tourze\Base\Object;

abstract class StorageBase extends Object
{

    /**
     * @var array
     */
    public $fields;

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
    public $cache = false;

    /**
     * 获取当前资源请求的字段
     *
     * @return array
     */
    public function getSourceColumns()
    {
        if (isset($this->fields))
        {
            $columns = array_keys($this->fields);
        }
        else
        {
            $columns = [];
        }

        return $columns;
    }
}
