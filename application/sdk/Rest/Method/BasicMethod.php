<?php

namespace sdk\Rest\Method;

/**
 * 最基础方法的接口
 */
interface BasicMethod
    extends GetMethod,
    PutMethod,
    PostMethod,
    DeleteMethod
{
}
