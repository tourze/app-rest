<?php

namespace rest\storage;

class Csv extends Base implements StorageInterface
{
    /**
     * 创建数据
     *
     * @param      $data
     * @param null $primaryID
     * @return  mixed
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
        // TODO: Implement record() method.
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
