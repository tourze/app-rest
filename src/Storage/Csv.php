<?php

namespace rest\Storage;

use EasyCSV\Reader;

class Csv extends StorageBase implements StorageInterface
{

    /**
     * @var Reader
     */
    public $reader = null;

    public $writer = null;

    public $file = null;

    public $delimiter = "\t";

    public $enclosure = '"';

    /**
     * @return \EasyCSV\Reader
     */
    public function ensureReader()
    {
        if ($this->reader === null)
        {
            $this->reader = new Reader($this->file);
            $this->reader->setDelimiter($this->delimiter);
            $this->reader->setEnclosure($this->enclosure);
        }

        return $this->reader;
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
        // TODO: Implement create() method.
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
        $i = 0;
        $records = [];
        $conditionsLineCount = count($conditions);

        $startLine = $offset + 2; // 一般第一行都作为csv的字段名了，所以从第二行开始
        $resourceColumns = $this->getSourceColumns();

        while ($row = $this->ensureReader()->getRow())
        {
            if ($this->ensureReader()->getLineNumber() < $startLine)
            {
                continue;
            }

            $matchCount = 0;
            foreach ($conditions as $k => $v)
            {
                // 暂时不支持$v是数组的情形
                if (isset($row[$k]) && $row[$k] == $v)
                {
                    $matchCount++;
                }
            }

            if ($matchCount == $conditionsLineCount)
            {
                $addRow = [];
                foreach ($resourceColumns as $col)
                {
                    if (isset($row[$col]))
                    {
                        $addRow[$col] = $row[$col];
                    }
                    else
                    {
                        $addRow[$col] = null;
                    }
                }
                $records[] = $addRow;
                $i++;
            }

            if ($i >= $limit)
            {
                break;
            }
        }

        return $records;
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
