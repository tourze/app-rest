<?php

namespace sdk\Rest\Content;

use Doctrine\Common\Inflector\Inflector;
use sdk\Rest\RestContent;
use SimpleXMLElement;

class XmlContent extends RestContent
{

    /**
     * 转换数据
     *
     * @return string
     */
    public function transform()
    {
        $this->_rest->etag();
        $values = $this->_rest->result;
        $name = (string) $this->_rest->model;

        /**
         * @param array $vars
         * @param       $xml  SimpleXMLElement
         * @param       $node
         */
        $walk = function (array $vars, $xml, $node) use (&$walk)
        {
            foreach ($vars as $name => $value)
            {
                if (is_array($value))
                {
                    $name = is_int($name) ? $node : $name;
                    $sub = $xml->addChild($name);
                    $walk($value, $sub, Inflector::singularize($name));
                }
                else
                {
                    $xml->addChild($name, htmlentities($value, ENT_QUOTES));
                }
            }
        };

        $xmlString = '<?xml version="1.0" encoding="utf-8"?>';
        // Check for associative array
        $xmlString .= '<' . ((array_keys($values) !== range(0, count($values) - 1)) ? Inflector::singularize($name) : $name) . '/>';

        $xml = new SimpleXMLElement($xmlString);

        $walk($values, $xml, Inflector::singularize($name));

        return $xml->asXML();
    }
}
