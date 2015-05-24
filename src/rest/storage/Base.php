<?php

namespace rest\storage;

abstract class Base
{

    /**
     * @var \rest\Core
     */
    public $app;

    public $resourceName;

    public $resourceID;

    public $meta;
}
