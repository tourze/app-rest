<?php

namespace rest;

use Symfony\Component\Yaml\Yaml;

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
     * @throws \rest\exception\MetaException
     */
    public static function get($resource)
    {
        $resourceFile = ROOT_PATH . self::$resourceDir . DIRECTORY_SEPARATOR . $resource . '.yaml';
        if ( ! is_file($resourceFile))
        {
            return false;
        }

        // 读取yaml内容，并解析为php数组
        $resourceContent = file_get_contents($resourceFile);
        $resourceContent = Yaml::parse($resourceContent);

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
