<?php

namespace rest;

use Symfony\Component\Yaml\Yaml;
use tourze\Base\Helper\Arr;

/**
 * META信息处理
 *
 * @package rest
 */
class Meta
{
    
    public static $resourceDir = 'resource';

    /**
     * 获取指定resource的meta数据
     *
     * @param $resource
     * @return array|string
     * @throws \rest\Exception\MetaException
     */
    public static function get($resource)
    {
        $resourceFile = RESOURCE_PATH . $resource . '.yaml';
        if ( ! is_file($resourceFile))
        {
            return false;
        }

        // 读取yaml内容，并解析为php数组
        $resourceContent = file_get_contents($resourceFile);
        $resourceContent = Yaml::parse($resourceContent);

        // storage特别处理
        if ($storage = Arr::get($resourceContent, 'storage'))
        {
            if (isset($storage['extends']))
            {
                $extendStorage = file_get_contents(STORAGE_PATH . $storage['extends'] . '.yaml');
                $extendStorage = Yaml::parse($extendStorage);
                unset($storage['extends']);

                $storage = Arr::merge($extendStorage, $storage);
            }
            $resourceContent['storage'] = $storage;
        }

        return $resourceContent;
    }

    /**
     * 保存meta数据
     *
     * @param $resource
     * @param $meta
     */
    public static function set($resource, $meta)
    {
    }

    /**
     * 删除资源
     *
     * @param $resource
     */
    public static function delete($resource)
    {
    }
}
