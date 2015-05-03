<?php

namespace sdk\Rest;

use sdk\Mvc\Model\Model;

class RestDbModel extends Model
{

    /**
     * @var string 表名
     */
    protected $_restTableName = null;

    /**
     * 构造函数
     *
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->_tableName = REST_TABLE_PREFIX . $id;
        $this->_restTableName = $id;

        $this->_dbGroup = REST_DB_GROUP;

        parent::__construct();

        if (array_key_exists('date_created', $this->_object))
        {
            $this->_createdColumn = [
                'column' => 'date_created',
                'format' => true
            ];
        }
        if (array_key_exists('date_updated', $this->_object))
        {
            $this->_updatedColumn = [
                'column' => 'date_updated',
                'format' => true
            ];
        }
    }

    protected function _loadMultiResultFetcherClass()
    {
        return 'stdClass';
    }
}
