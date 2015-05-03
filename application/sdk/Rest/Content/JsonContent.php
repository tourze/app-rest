<?php

namespace sdk\Rest\Content;

use sdk\Rest\RestContent;

class JsonContent extends RestContent
{

    /**
     * 转换数据
     *
     * @return string
     */
    public function transform()
    {
        $this->_rest->etag();

        return json_encode($this->_rest->result);
    }
}
