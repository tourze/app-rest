<?php

namespace rest\storage;

use Doctrine\DBAL\DriverManager;

/**
 * PDO连接
 *
 * @package rest\storage
 */
class PDO extends Base implements StorageInterface
{

    /**
     * @var \Doctrine\DBAL\Connection
     */
    public $conn;

    /**
     * @var string 表名
     */
    public $table;

    public function __construct($params)
    {
        $dbParams = [
            'driver'   => $params['driver'],
            'host'     => $params['host'],
            'dbname'   => $params['dbname'],
            'user'     => $params['user'],
            'password' => $params['password'],
        ];

        $this->conn = DriverManager::getConnection($dbParams);

        $this->table = $params['table'];
    }

    /**
     * 创建数据
     *
     * @param      $data
     * @param null $primaryID
     * @return  bool
     */
    public function create($data, $primaryID = null)
    {
        // TODO: Implement create() method.
    }

    /**
     * 更新数据
     *
     * @param $primaryID
     * @param $data
     * @return bool
     */
    public function update($primaryID, $data)
    {
        // TODO: Implement update() method.
    }

    /**
     * 获取一个或多条记录
     *
     * @param array $conditions
     * @param int   $limit
     * @param int   $offset
     * @param null  $orderBy
     * @return mixed
     */
    public function record(array $conditions, $limit = 1, $offset = 0, $orderBy = null)
    {
        $query = $this->conn->createQueryBuilder();

        $columns = array_keys($this->meta['fields']);
        foreach ($columns as $column)
        {
            $query->addSelect($this->conn->quoteIdentifier($column));
        }
        $query->from($this->table);

        // 判断条件
        foreach ($conditions as $key => $value)
        {
            $query->where("$key = :$key");
            $query->setParameter($key, $value);
        }

        if ($orderBy)
        {
            if (is_array($orderBy))
            {
                foreach ($orderBy as $sort => $order)
                {
                    $query->orderBy($sort, $order);
                }
            }
            else
            {
                $query->orderBy($orderBy);
            }
        }
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        $result = $query->execute()->fetchAll();

        return $result;
    }

    /**
     * 删除指定记录
     *
     * @param $primaryID
     * @return bool
     */
    public function delete($primaryID)
    {
        // TODO: Implement delete() method.
    }
}
