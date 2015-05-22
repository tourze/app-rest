<?php

namespace rest;

use rest\storage\StorageInterface;

/**
 * Class Storage
 *
 * @package rest
 */
class Storage
{

    public static $defaultType = 'csv';

    public static $typeMapping = [
        'csv'  => '\rest\storage\Csv',
        'pdo'  => '\rest\storage\PDO',
        'fake' => '\rest\storage\Faker',
    ];

    /**
     * @param $config
     * @return StorageInterface
     */
    public static function instance($config)
    {
        $driver = (isset($config['type']) && isset(self::$typeMapping[$config['type']])) ? $config['type'] : self::$defaultType;
        $class = self::$typeMapping[$driver];

        /** @var StorageInterface $instance */
        $instance = new $class($config);

        return $instance;
    }

}
