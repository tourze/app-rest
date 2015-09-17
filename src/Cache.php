<?php

namespace rest;

use tourze\Base\Base;
use tourze\Base\Object;

class Cache extends Object
{

    /**
     * 返回指定实例
     *
     * @param array $config
     * @return \rest\Cache
     */
    public static function instance($config = [])
    {
        return new self($config);
    }

    /**
     * 获取缓存
     *
     * @param string $key
     * @return string
     */
    public function get($key)
    {
        return Base::getCache()->get($key);
    }

    /**
     * 保存缓存
     *
     * @param string $key
     * @param mixed  $data
     * @param int    $expire 过期时间
     */
    public function set($key, $data, $expire = 600)
    {
        Base::getCache()->set($key, $data, $expire);
    }

    /**
     * 强制删除缓存
     *
     * @param string $key
     */
    public function del($key)
    {
        Base::getCache()->remove($key);
    }
}
