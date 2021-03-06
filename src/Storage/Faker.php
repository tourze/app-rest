<?php

namespace rest\Storage;
use Faker\Factory;

/**
 * Faker数据源
 *
 * @package rest\Storage
 */
class Faker extends StorageBase implements StorageInterface
{

    /**
     * @var bool Faker默认不使用缓存
     */
    public $cache = false;

    /**
     * 创建数据
     *
     * @param      $data
     * @param null $primaryID
     * @return  mixed
     */
    public function create($data, $primaryID = null)
    {
        $faker = Factory::create();

        $data['id'] = $faker->randomDigitNotNull;
        return [$data];
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
        $result = [];

        // 获取单挑记录
        if ($limit == 1)
        {
            $record = $this->generateFakeRecord();
            $record = array_merge($record, $conditions);

            $result[] = $record;
        }
        else
        {
            $i = 0;
            while ($i < $limit)
            {
                $result[] = $this->generateFakeRecord();
                $i++;
            }
        }

        return $result;
    }

    /**
     * 生成一条用于测试的记录
     *
     * @return array
     */
    public function generateFakeRecord()
    {
        $faker = Factory::create();
        $result = [];

        foreach ($this->fields as $field => $fieldConfig)
        {
            try
            {
                $value = $faker->$field;
            }
            catch (\InvalidArgumentException $e)
            {
                if ($fieldConfig['type'] == 'integer')
                {
                    $value = $faker->randomDigitNotNull;
                }
                else
                {
                    $value = $field;
                }
            }

            $result[$field] = $value;
        }

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
