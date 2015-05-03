<?php

namespace sdk\Rest\Controller;

use sdk\Mvc\Controller;
use sdk\Rest;
use sdk\Rest\RestContent;

abstract class RestController extends Controller implements Rest\RestController
{

    protected $_useRest = true;

    /**
     * Rest object
     *
     * @var Rest
     */
    protected $_rest;

    public function before()
    {
        parent::before();

        if ($this->_useRest)
        {
            $this->_rest = Rest::instance([
                'controller' => $this,
                'request'    => $this->request,
                'response'   => $this->response,
            ])
                ->methodOverride(true)
                ->contentOverride(true)
                ->execute();
        }
    }

    protected function contentHandle($type)
    {
        $this->response->body = RestContent::handle($type, $this->_rest);
    }

    public function actionHtml()
    {
        $this->contentHandle('HtmlHelper');
    }

    public function actionJson()
    {
        $this->contentHandle('Json');
    }

    public function actionXml()
    {
        $this->contentHandle('Xml');
    }

    public function actionCsv()
    {
        $this->contentHandle('Csv');
    }

}
