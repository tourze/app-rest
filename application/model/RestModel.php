<?php

namespace model;

use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;
use sdk\Base\Helper\Arr;
use sdk\Mvc\Model\Model;
use sdk\Rest;
use sdk\Rest\RestDb;
use sdk\Rest\RestDbModel;
use sdk\Base\Security\Validation;

/**
 * 一个特殊的模型，用于返回当前用户可操作的model
 *
 * @property array  columns
 * @property array  source
 * @property string name
 * @property string description
 * @property array  access
 * @property mixed  title
 * @package model
 */
class RestModel extends Model
{

    protected $_tableName = 'rest_model';

    protected $_dbGroup = 'rest';

    /**
     * @var  array  创建记录时自动附加上当前时间戳
     */
    protected $_createdColumn = [
        'column' => 'date_created',
        'format' => true
    ];

    /**
     * @var  array  更新记录时自动附加上当前时间戳
     */
    protected $_updatedColumn = [
        'column' => 'date_updated',
        'format' => true
    ];

    protected $_serializeColumns = ['columns', 'source', 'access'];

    /**
     * 保存时，修正一些选项信息
     *
     * @param \sdk\Base\Security\Validation $validation
     * @return \sdk\Mvc\Model\Model
     * @throws \sdk\Base\Exception\BaseException
     */
    public function create(Validation $validation = null)
    {
        if ( ! $this->columns)
        {
            $this->columns = json_encode([]);
        }
        else
        {
            $columns = $this->columns;
            foreach ($columns as $k => $v)
            {
                if (isset($v['$editing']))
                {
                    unset($v['$editing']);
                    $columns[$k] = $v;
                }
            }
            $this->columns = $columns;
        }

        if ( ! $this->source)
        {
            $this->source = json_encode(['type' => 0]);
        }
        if ( ! $this->access)
        {
            $this->access = json_encode([
                'request' => [],
                'time'    => [],
            ]);
        }
        if ( ! $this->description)
        {
            $this->description = '';
        }

        $result = parent::create($validation);
        $this->fixTable();
        return $result;
    }

    /**
     * 保存时，修正一些选项信息
     *
     * @param \sdk\Base\Security\Validation $validation
     * @return \sdk\Mvc\Model\Model
     * @throws \sdk\Base\Exception\BaseException
     */
    public function update(Validation $validation = null)
    {
        if ( ! $this->columns)
        {
            $this->columns = [];
        }
        else
        {
            $columns = $this->columns;
            foreach ($columns as $k => $v)
            {
                if (isset($v['$editing']))
                {
                    unset($v['$editing']);
                    $columns[$k] = $v;
                }
            }
            $this->columns = $columns;
        }

        if ( ! $this->source)
        {
            $this->source = ['type' => 0];
        }
        if ( ! $this->access)
        {
            $this->access = [
                'request' => [],
                'time'    => [],
            ];
        }
        if ( ! $this->description)
        {
            $this->description = '';
        }

        $result = parent::update($validation);
        $this->fixTable();
        return $result;
    }

    /**
     * 重载原get方法，实现一些数据的修复
     *
     * @param string $column
     * @return array|mixed
     * @throws \sdk\Base\Exception\BaseException
     */
    public function get($column)
    {
        $value = parent::get($column);

        // 如果字段数据为空，那么要自动读一次
        if ($column === 'columns' && ! $value)
        {
            $tableName = Rest::formatTableName($this->name);
            $dbModel = new RestDbModel($tableName);
            $value = RestDb::formatColumnConfig($dbModel->listColumns());

            // 保存字段数据
            // 这里还应该加个缓存机制，自动刷新
            //$this->columns = $value;
            //$this->save();
        }

        // source处理
        if ($column === 'source' && ! $value)
        {
            $value = ['type' => 0];
        }

        // access处理
        if ($column === 'access' && ! $value)
        {
            $value = [
                'request' => [],
                'time'    => [],
            ];
        }

        return $value;
    }

    /**
     * 删除model记录的同时，删除数据库
     */
    public function delete()
    {
        $schemaManager = $this->db()->getSchemaManager();
        $tableName = REST_TABLE_PREFIX . Rest::formatTableName($this->name);

        $result = parent::delete();

        if ($schemaManager->tablesExist($tableName))
        {
            $schemaManager->dropTable($tableName);
        }

        return $result;
    }

    /**
     * 修正模型对应的数据库
     */
    public function fixTable()
    {
        $schemaManager = $this->db()->getSchemaManager();
        $tableName = REST_TABLE_PREFIX . Rest::formatTableName($this->name);

        // 根据当前用户提交的数据，生成一个虚拟表

        $virtualTable = new Table($tableName);

        $virtualTable->addColumn('id', 'bigint', [
            'length'        => 20,
            'unsigned'      => true,
            'autoincrement' => true
        ]);

        // 增加用户提交过来的字段数据
        foreach ($this->columns as $column)
        {
            $name = Arr::get($column, 'name');
            $type = Arr::get($column, 'type');
            $options = [
                'length'  => Arr::get($column, 'length'),
                'notnull' => Arr::get($column, 'require'),
                'comment' => Arr::get($column, 'title'),
                'default' => Arr::get($column, 'default'),
            ];
            $virtualTable->addColumn($name, $type, $options);
        }

        $virtualTable->addColumn('date_created', 'integer', [
            'length'  => 10,
            'default' => 0,
            'notnull' => false,
        ]);
        $virtualTable->addColumn('date_updated', 'integer', [
            'length'  => 10,
            'default' => 0,
            'notnull' => false,
        ]);
        $virtualTable->addOption('comment', $this->title);
        $virtualTable->setPrimaryKey(['id']);

        // 下面开始表的处理逻辑
        // 表还不存在，那么新建
        if ( ! $schemaManager->tablesExist($tableName))
        {
            $schemaManager->createTable($virtualTable);
        }
        else
        {
            $realTable = $schemaManager->listTableDetails($tableName);
            $comparator = new Comparator();
            $tableDiff = $comparator->diffTable($realTable, $virtualTable);

            if ($tableDiff instanceof TableDiff)
            {
                $schemaManager->alterTable($tableDiff);
            }
        }
    }
}
