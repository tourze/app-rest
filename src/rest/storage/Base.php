<?php

namespace rest\storage;

use Predis\Client;

abstract class Base
{

    /**
     * @var \rest\Core
     */
    public $app;

    public $resourceName;

    public $resourceID;

    public $meta;

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
     * @param $params
     * @return string
     */
    public function getCache($params)
    {
        $key = $this->generateCacheKey([
            'resourceID'   => $this->app->resourceID,
            'resourceName' => $this->app->resourceName,
            'resourcePath' => $this->app->resourcePath,
            'params'       => $params,
        ]);
        return $this->ensureCache()->get($key);
    }

    /**
     * 保存缓存
     *
     * @param $params
     * @param $data
     * @param $expire integer 过期时间
     * @return mixed
     */
    public function setCache($params, $data, $expire = 600)
    {
        $key = $this->generateCacheKey([
            'resourceID'   => $this->app->resourceID,
            'resourceName' => $this->app->resourceName,
            'resourcePath' => $this->app->resourcePath,
            'params'       => $params,
        ]);
        return $this->ensureCache()->set($key, $data, $expire);
    }

    /**
     * 强制删除缓存
     *
     * @param $params
     * @return int
     */
    public function deleteCache($params)
    {
        $key = $this->generateCacheKey([
            'resourceID'   => $this->app->resourceID,
            'resourceName' => $this->app->resourceName,
            'resourcePath' => $this->app->resourcePath,
            'params'       => $params,
        ]);
        return $this->ensureCache()->del($key);
    }
}
