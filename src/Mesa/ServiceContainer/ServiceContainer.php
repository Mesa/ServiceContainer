<?php

namespace Mesa\ServiceContainer;

use Mesa\Exception\ServiceException;

class ServiceContainer
{

    protected $container = array();

    public function createService($name, $namespace, $arguments = array(), $static = false)
    {
        if (trim($name) == "" || trim($namespace) == "") {
            throw new ServiceException('Service name was empty');
        }
        $service = new Service();
        $service->setName($name)
                ->setNamespace($namespace);

        if (count($arguments) > 0) {
            array_walk_recursive($arguments, array($this,'parseArgumentValue'));
            foreach ($arguments as $argName => $argValue) {
                $service->addArgument($argName, $argValue);
            }
        }

        $service->setStatic($static);
        return $service;
    }

    protected function parseArgumentValue(&$item, &$value)
    {
        if (is_string($item) &&  substr($item, 0, 1) == "%" ) {
            $item = $this->get(str_replace('%', '', $item));

        } elseif (is_string($item) && substr($item, 0, 1) == "!" ) {
            $item = $this
                ->get('yaml')
                ->parse(
                    substr($item, 1, strlen($item))
                );

        } elseif (is_string($item) && substr($item, 0, 1) == "@" ) {
            $item = str_replace('@', '', $item);
        }
    }

    public function get ($name)
    {
        if (isset($this->container[$name])) {
            return $this->container[$name]->getClass();
        } elseif ($name == "ServiceContainer") {
            return $this;
        } else {
            throw new ServiceException('Service with name [' . $name . "] does not exist");
        }
    }

    public function getByNamespace ($namespace)
    {
        if (isset($this->namespaceContainer[$namespace])) {
            return $this->namespaceContainer[$namespace]->getClass();
        } elseif ($namespace == "\Mesa\ServiceContainer\ServiceContainer") {
            return $this;
        }

        throw new ServiceException('Service with namespace [' . $namespace . '] not found');
    }

    public function add (Service $service)
    {
        $this->container[$service->getName()] = $service;
        $this->namespaceContainer[$service->getNamespace()] = &$service;
        return true;
    }

    public function remove(Service $service)
    {
        if ($this->exist($service->getName())) {
            unset($this->container[$service->getName()]);
        }

        if (isset($this->namespaceContainer[$service->getNamespace()])) {
            unset($this->namespaceContainer[$service->getNamespace()]);
        }
    }

    public function exist($name)
    {
        return isset($this->container[$name]);
    }

    public function existsNamespace($namespace)
    {
        return isset($this->namespaceContainer[$namespace]);
    }
}
