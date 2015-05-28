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
    public $conn = null;

    /**
     * @var string 表名
     */
    public $table;

    /**
     * @var string  PDO使用的驱动
     */
    public $driver;

    /**
     * @var string 主机地址
     */
    public $host;

    /**
     * @var string 数据库名
     */
    public $dbname;

    /**
     * @var string 数据库用户
     */
    public $user;

    /**
     * @var string 连接密码
     */
    public $password;

    /**
     * 确保返回一个PDO对象
     *
     * @return \Doctrine\DBAL\Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public function ensurePDO()
    {
        if ($this->conn === null)
        {
            $dbParams = [
                'driver'   => $this->driver,
                'host'     => $this->host,
                'dbname'   => $this->dbname,
                'user'     => $this->user,
                'password' => $this->password,
            ];
            $this->conn = DriverManager::getConnection($dbParams);
        }

        return $this->conn;
    }

    /**
     * 创建数据
     *
     * @param      $data
     * @param null $primaryID
     * @return  mixed
     */
    public function create($data, $primaryID = null)
    {
        if ($primaryID !== null)
        {
            $data['id'] = $primaryID;
        }

        $query = $this->ensurePDO()->createQueryBuilder();
        $query->insert($this->table);

        $insertData = [];
        foreach ($data as $k => $v)
        {
            $insertData[$this->ensurePDO()->quoteIdentifier($k)] = ":$k";
        }
        $query->values($insertData);
        $query->setParameters($data);

        $query->execute();
        $id = $this->ensurePDO()->lastInsertId();

        if ($id)
        {
            return $this->record(['id' => $id]);
        }
        else
        {
            return false;
        }
    }

    /**
     * 更新数据
     *
     * @param $primaryID
     * @param $data
     * @return mixed
     */
    public function update($primaryID, $data)
    {
        $query = $this->ensurePDO()->createQueryBuilder();

        $query->update($this->table);

        // 更新条件
        $query->where('id = :id');
        $query->setParameter('id', $primaryID);

        // 更新内容
        foreach ($data as $k => $v)
        {
            $query->set($k, ":$k");
            $query->setParameter($k, $v);
        }

        $query->execute();
        return true;
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
        $query = $this->ensurePDO()->createQueryBuilder();

        $columns = $this->getSourceColumns();
        foreach ($columns as $column)
        {
            $query->addSelect($this->ensurePDO()->quoteIdentifier($column));
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
        $query = $this->ensurePDO()->createQueryBuilder();

        $query->delete($this->table);
        $query->where('id = :id');
        $query->setParameter('id', $primaryID);

        return (bool) $query->execute();
    }
}
