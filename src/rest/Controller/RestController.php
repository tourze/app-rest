<?php

namespace rest\Controller;

use rest\Core;
use tourze\Controller\BaseController;

class RestController extends BaseController
{

    /**
     * @var Core
     */
    public $rest;

    public function before()
    {
        parent::before();
    }

    public function actionIndex()
    {
        $resource = $this->request->param('resource');

        $this->rest = new Core([
            'request'  => $this->request,
            'response' => $this->response,
        ]);
        $this->rest->loadResource($resource);
        $this->rest->loadQuery();
        $this->rest->loadData();
        $this->rest->loadBehavior();
        $this->rest->dispatchMethod();
    }

}
