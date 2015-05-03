<?php

namespace sdk\Rest;

use sdk\Base\Object;
use sdk\Rest;

/**
 * Class RestContent
 *
 * @package sdk\Rest
 * @property  $rest    RestCore
 */
abstract class RestContent extends Object
{

    /**
     * @var  Rest
     */
    protected $_rest;

    protected $_data;
    protected $_config = [];

    protected $_result;

    public static function handle($type, Rest $rest)
    {
        $type = ucfirst($type);
        $class = '\sdk\Rest\Content\\'.$type.'Content';
        /** @var RestContent $instance */
        $instance = new $class;
        $instance->rest = $rest;

        return $instance->transform();
    }

    /**
     * 转换数据
     *
     * @return string
     */
    abstract public function transform();

    /**
     * @return mixed
     */
    public function getRest()
    {
        return $this->_rest;
    }

    /**
     * @param mixed $rest
     */
    public function setRest($rest)
    {
        $this->_rest = $rest;
    }
}
