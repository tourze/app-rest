<?php

namespace sdk\Rest\Method;

/**
 * 一个完整方法的接口
 */
interface AllMethod
    extends BasicMethod,
    HeadMethod,
    TraceMethod,
    PatchMethod,
    OptionsMethod
{
}
