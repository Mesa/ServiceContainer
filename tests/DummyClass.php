<?php

namespace Mesa\ServiceContainer;

class DummyClass
{
    public function __construct($first, $second, \Mesa\ServiceContainer\Wrapper $third)
    {
    }
}

class EmptyConstructor
{
    public function __construct()
    {
    }

    public function noParam()
    {
    }

    public function returnParam($param)
    {
        return $param;
    }
}
