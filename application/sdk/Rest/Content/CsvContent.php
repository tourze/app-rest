<?php

namespace sdk\Rest\Content;

use sdk\Rest\RestContent;

class CsvContent extends RestContent
{

    /**
     * 转换数据
     *
     * @return string
     */
    public function transform()
    {
        $filename = (string) $this->_rest->model;
        $this->_rest->response->headers('Content-disposition', 'filename=' . $filename . '.csv');

        $csv = '';
        $values = $this->_rest->result;
        if (empty($values))
        {
            return $csv;
        }

        $titles = function (array $vars, $node) use (&$titles)
        {
            $result = [];
            foreach ($vars as $name => $value)
            {
                if (is_array($value))
                {
                    $name = is_int($name) ? $node . '_' . $name : $name;
                    $result[] = $titles($value, $name);
                }
                else
                {
                    $result[] = empty($node) ? $name : $node . '.' . $name;
                }
            }

            return implode('","', $result);
        };

        $walk = function (array $vars) use (&$walk)
        {
            $result = [];
            foreach ($vars as $name => $value)
            {
                if (is_array($value))
                {
                    $result[] = $walk($value);
                }
                else
                {
                    $result[] = str_replace('"', '""', $value);
                }
            }

            return implode('","', $result);
        };

        // Check for associative array
        if (array_keys($values) !== range(0, count($values) - 1))
        {
            $csv = '"' . $titles($values, '') . "\"\n";
            $csv .= '"' . $walk($values) . "\"\n";
        }
        else
        {
            $csv = '"' . $titles($values[0], '') . "\"\n";
            foreach ($values as $row)
            {
                $csv .= '"' . $walk($row) . "\"\n";
            }
        }

        return $csv;
    }
}
