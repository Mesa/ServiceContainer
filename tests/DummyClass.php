<?php

namespace Mesa\ServiceContainer;

class DummyClass
{
    public function __construct($first, $second, \Mesa\ServiceContainer\Service $third)
    {
    }
}

class EmptyConstructor
{
    public function __construct()
    {
    }
}
