<?php

namespace rest;

use sdk\Rest\RestModel;

/**
 * 消息提醒
 *
 * @package rest
 */
class Message extends RestModel
{

    protected $_restModel = '\model\RestMessage';

    protected $_modelName = '消息';
    
}
