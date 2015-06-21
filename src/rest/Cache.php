<?php

namespace rest;

use Predis\Client;
use tourze\Redis\Redis;

class Cache
{

    /**
     * @var Client 默认使用redis作为缓存
     */
    public $redis = null;

    /**
     * @return \tourze\Redis\Client
     */
    public function ensureCache()
    {
       return Redis::instance();
    }

    public function generateCacheKey()
    {
        $params = func_get_args();
        $params = serialize($params);

        return (string) sha1($params);
    }

    /**
     * 获取缓存
     *
     * @param $key
     * @return string
     */
    public function get($key)
    {
        return $this->ensureCache()->get($key);
    }

    /**
     * 保存缓存
     *
     * @param $key
     * @param $data
     * @param $expire integer 过期时间
     */
    public function set($key, $data, $expire = 600)
    {
        $this->ensureCache()->set($key, $data);
        $this->ensureCache()->expire($key, $expire);
    }

    /**
     * 强制删除缓存
     *
     * @param $key
     * @return int
     */
    public function del($key)
    {
        return $this->ensureCache()->del($key);
    }
}
