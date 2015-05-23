<?php

namespace rest\storage;

abstract class Base
{

    public $resourceName;

    public $resourceID;

    public $meta;

    /**
     * 获取缓存
     *
     * @param $params
     */
    public function getCache($params)
    {
    }

    /**
     * 保存缓存
     *
     * @param $params
     * @param $data
     * @param $expire integer 过期时间
     */
    public function setCache($params, $data, $expire)
    {
    }

    /**
     * 强制删除缓存
     *
     * @param $params
     */
    public function deleteCache($params)
    {
    }
}
