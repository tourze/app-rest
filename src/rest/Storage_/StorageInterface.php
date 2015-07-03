<?php

namespace rest\Storage;

interface StorageInterface
{

    /**
     * 创建数据
     *
     * @param      $data
     * @param null $primaryID
     * @return  mixed
     */
    public function create($data, $primaryID = null);

    /**
     * 更新数据
     *
     * @param $primaryID
     * @param $data
     * @return mixed
     */
    public function update($primaryID, $data);

    /**
     * 获取一个或多条记录
     *
     * @param array $conditions
     * @param int   $limit
     * @param int   $offset
     * @param null  $orderBy
     * @return mixed
     */
    public function record(array $conditions, $limit = 1, $offset = 0, $orderBy = null);

    /**
     * 删除指定记录
     *
     * @param $primaryID
     * @return bool
     */
    public function delete($primaryID);
}
