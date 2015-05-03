<?php

namespace sdk\Rest\Content;

use sdk\Rest\RestContent;
use sdk\Mvc\View;

class HtmlContent extends RestContent
{

    /**
     * 转换数据
     *
     * @return string
     */
    public function transform()
    {
        $values = $this->rest->result;
        $view = View::factory('rest/html', ['values' => $values]);

        return $view->render();
    }
}
