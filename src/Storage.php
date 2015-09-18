<?php

namespace rest;

use rest\Storage\StorageBase;
use rest\Storage\StorageInterface;

/**
 * Class Storage
 *
 * @package rest
 */
class Storage
{

    public static $defaultType = 'csv';

    public static $typeMapping = [
        'pdo'  => '\rest\Storage\PDO',
        'fake' => '\rest\Storage\Faker',
        'csv' => '\rest\Storage\CSV',
    ];

    /**
     * @param       $config
     * @param array $fields
     * @return StorageInterface
     */
    public static function instance($config, $fields = [])
    {
        $driver = (isset($config['type']) && isset(self::$typeMapping[$config['type']])) ? $config['type'] : self::$defaultType;
        $class = self::$typeMapping[$driver];

        /** @var StorageBase $instance */
        $instance = new $class($config);
        $instance->fields = $fields;

        return $instance;
    }

}
