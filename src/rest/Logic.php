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
        'date_created'    => '\rest\logic\DateCreated',
        'date_updated'    => '\rest\logic\DateUpdated',
        'result_not_null' => '\rest\logic\ResultNotNull',
    ];

}
