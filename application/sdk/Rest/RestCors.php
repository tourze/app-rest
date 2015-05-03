<?php

namespace sdk\Rest;

use sdk\Rest;
use sdk\Rest\Method\OptionsMethod;

/**
 * Cors相关
 *
 * @package        jerfowler/REST
 * @author         Jeremy Fowler
 * @copyright  (c) 2012 Jeremy Fowler
 */
interface RestCors extends OptionsMethod
{
    public function restCors(Rest $rest);
}
