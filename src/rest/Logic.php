<?php

namespace rest;

/**
 * 逻辑处理
 *
 * @package rest
 */
class Logic
{

    public $classMap = [
        'date_created'    => '\rest\Logic\DateCreated',
        'date_updated'    => '\rest\Logic\DateUpdated',
        'result_not_null' => '\rest\Logic\ResultNotNull',
    ];

}
