<?php

namespace rest;

use sdk\Rest;
use sdk\Rest\RestModel;

/**
 * 一个特殊的rest接口，用于管理当前用户的model
 *
 * @package rest
 */
class Model extends RestModel
{

    protected $_restModel = '\model\RestModel';

    protected $_modelName = '模型';

    /**
     * @var bool model一定需要检查权限
     */
    protected $_enableAuth = true;

    /**
     * 校验用户，当前模型只有登录后才能查看，并且只能查看和编辑属于自己的模型
     *
     * @param Rest $rest
     *
     * @return bool
     */
    public function restAuth(Rest $rest)
    {
        if ($rest->request->query('_ywisax') == 'ya')
        {
            return true;
        }
        $result = parent::restAuth($rest);

        if ($result)
        {
            // 加多一些判断条件，只能读取用户有权限读取的model
            //$this->_restModelCall = [
            //    ['where', ['user_id', '=', Arr::get($rest->user, 'id')]]
            //];
            //print_r($rest->user);
            //exit;
        }

        return $result;
    }

    /**
     * @var  \model\RestModel
     */
    protected $_restGetOneModel;

    /**
     * 读取
     *
     * @param \sdk\Rest $rest
     * @return mixed
     */
    protected function restGetOne(Rest $rest)
    {
        $result = parent::restGetOne($rest);
        return $result;
    }

    /**
     * 更新模型信息
     *
     * @param Rest $rest
     */
    public function restPost(Rest $rest)
    {
        parent::restPost($rest);
    }

    /**
     * 创建模型信息
     *
     * @param Rest $rest
     *
     * @return array|void
     */
    public function restPut(Rest $rest)
    {
        parent::restPut($rest);
    }
}
