<?php

namespace sdk\Rest;

use sdk\Rest;

/**
 * AUTH权限判断
 */
interface RestAuth
{
    public function restAuth(Rest $rest);
}
