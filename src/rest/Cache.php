<?php

namespace rest;

use Predis\Client;

class Cache
{

    /**
     * @var \rest\Core
     */
    public $app;

    /**
     * @var Client 默认使用redis作为缓存
     */
    public $redis = null;

    /**
     * @return \Predis\Client
     */
    public function ensureCache()
    {
        if ($this->redis === null)
        {
            // redis操作相关
            $redisConfig = $this->app->loadConfig('redis');
            $this->redis = new Client($redisConfig['conn'], $redisConfig['options']);
        }

        return $this->redis;
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
    {;
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
