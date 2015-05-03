<?php

namespace sdk\Rest;

use sdk\Mvc\Model\Exception\ModelException;
use sdk\Mvc\Model\Model;
use sdk\Rest;
use sdk\Rest\Exception\RestException;

/**
 * 直接关联数据库和REST
 *
 * @property  string  sourceTable
 */
class RestDb extends RestModel
{

    /**
     * @var string 源数据表
     */
    protected $_sourceTable;

    /**
     * 构造函数
     *
     * @param array $args
     */
    public function __construct($args = [])
    {
        foreach ($args as $k => $v)
        {
            $this->{$k} = $v;
        }
        $this->formatTableName();
    }

    /**
     * 额外的权限判断
     *
     * @param \sdk\Rest $rest
     *
     * @return bool
     */
    public function restAuth(Rest $rest)
    {
        $result = parent::restAuth($rest);

        if ($result)
        {
            // 加载相关配置
        }

        return $result;
    }

    /**
     * 根据源表名，返回一个有效的AR模型
     *
     * @return Model
     * @throws RestException
     */
    public function prepareModel()
    {
        if ($this->_sourceTable)
        {
            try
            {
                return new RestDbModel($this->_sourceTable);
            }
            catch (ModelException $e)
            {
                throw new RestException($e->getMessage());
            }
        }
        else
        {
            return parent::prepareModel();
        }
    }

    /**
     * 判断指定的表是否存在
     *
     * @return bool
     */
    public function valid()
    {
        return false;
    }

    /**
     * 格式化表名
     */
    public function formatTableName()
    {
        $this->_sourceTable = str_replace(['/', '-'], '_', $this->_sourceTable);
    }

    /**
     * @return string
     */
    public function getSourceTable()
    {
        return $this->_sourceTable;
    }

    /**
     * @param string $sourceTable
     */
    public function setSourceTable($sourceTable)
    {
        $this->_sourceTable = $sourceTable;
    }

    /**
     * @var array 返回字段数据时，需要跳过的字段
     */
    public static $excludeColumns = ['id', 'date_created', 'date_updated'];

    /**
     * 格式化字段数据
     *
     * @param $columns array
     *
     * @return array
     */
    public static function formatColumnConfig($columns)
    {
        $result = [];

        /** @var \Doctrine\DBAL\Schema\Column $columnObject */
        foreach ($columns as $columnName => $columnObject)
        {
            // 统一不返回id、date_created、date_updated字段，因为这些字段是必带的
            if (in_array($columnName, self::$excludeColumns))
            {
                continue;
            }

            $addColumn = [
                'name'           => $columnName,
                'title'          => $columnObject->getComment() ? $columnObject->getComment() : $columnName,
                'type'           => $columnObject->getType()->getName(),
                'length'         => $columnObject->getLength(),
                'require'        => $columnObject->getNotnull(),
                'default'        => $columnObject->getDefault(),
                'config'         => [],
            ];

            switch ($addColumn['type'])
            {
                case 'enum':
                    $addColumn['config']['options'] = $columnObject->getCustomSchemaOptions();
                default:
                    //
            }

            // 部分配置直接写在注释中
            if (strpos($addColumn['title'], '|') !== false)
            {
                list($title, $config) = explode('|', $addColumn['title'], 2);
                $addColumn['title'] = $title;
                $addColumn['config'] = json_decode($config);
            }

            // config配置会优先覆盖其他设置
            foreach ($addColumn['config'] as $k => $v)
            {
                if ($k != 'config' && isset($addColumn[$k]))
                {
                    $addColumn[$k] = $v;
                }
            }

            $result[] = $addColumn;
        }

        return $result;
    }
}
