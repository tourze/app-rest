<?php

namespace controller;

use sdk\Base\Sdk;
use sdk\Rest\Controller\RestController as BaseRestController;
use sdk\Rest\RestModel;
use sdk\Mvc\Route;

/**
 * REST控制器，对SDK框架的rest功能做一次包装
 *
 * @category   Controller
 * @author     YwiSax <25803471@qq.com>
 */
class RestController extends BaseRestController
{

    public function before()
    {
        if ('rest-index' == $this->request->route->name())
        {
            $this->_useRest = false;
        }
        parent::before();
    }

    public function actionMain()
    {
        // 获取所有有效的rest菜单
        $restUrls = [];
        $classes = Sdk::listFiles('rest');
        foreach ($classes as $class => $path)
        {
            // 让类名规范点
            $class = '\\' . str_replace(['/', '.php'], ['\\', ''], $class);
            /** @var RestModel $restObject */
            $restObject = new $class;
            $restUrls[$restObject->modelName] = [
                'url'   => Route::url('rest', ['action' => $restObject->modelName], $this->request),
                'desc'  => $restObject->modelDesc,
                'allow' => [
                    'get'     => $restObject->allowGet(),
                    'post'    => $restObject->allowPost(),
                    'put'     => $restObject->allowPut(),
                    'delete'  => $restObject->allowDelete(),
                    'options' => $restObject->allowOptions(),
                    'head'    => $restObject->allowHead(),
                ],
            ];
        }

        $this->response->headers('Content-Type', 'application/json');
        $this->response->body = json_encode($restUrls);
    }
}
