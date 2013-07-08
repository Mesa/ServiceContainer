<?php

namespace Mesa\ServiceContainer;

class ServiceContainer
{

    protected $container = array();
    protected $namespaceContainer = array();
    /**
     * Create Service Class
     * 
     * @deprecated Will be removed in future. Please use $this->addService
     * 
     * @return [Object] Mesa\ServiceContainer\Service 
     **/
    public function createService($alias, $namespace, $arguments = array(), $static = false)
    {
        if (trim($alias) == "" || trim($namespace) == "") {
            throw new ServiceException('Service alias/Namespace was empty');
        }
        $wrapper = new Wrapper($namespace);
        $wrapper->setAlias($alias);

        if (count($arguments) > 0) {
            array_walk_recursive($arguments, array($this,'parseArgumentValue'));
            foreach ($arguments as $argName => $argValue) {
                $wrapper->addParam($argName, $argValue);
            }
        }

        $wrapper->setStatic($static);
        return $wrapper;
    }

    /**
     * Call Service Method and get returned Value
     *
     * @return Mixed
     **/
    public function call($alias, $method_name, $parameters = array())
    {
        if ($this->exists($alias)) {
            $wrapper = $this->container[$alias];
        } elseif ($this->existsNamespace($alias)) {
            $wrapper = $this->namespaceContainer[$alias];
        } else {
            throw new \InvalidArgumentException("No Service found with alias/namespace with " . $alias);
        }

        if (!$wrapper->hasMethod($method_name)) {
            throw new \InvalidArgumentException(
                'Class ' . $wrapper->getNamespace() . ' has no method called ' . $method_name
            );
        }

        foreach ($parameters as $argName => $argValue) {
            $wrapper->addParam($argName, $argValue);
        }

        return $wrapper->call($method_name);
    }

    /**
     * Wrapper Class for $this->createService && $this->add
     **/
    public function addService($alias, $namespace, $arguments = array(), $static = false)
    {
        $this->add($this->createService($alias, $namespace, $arguments, $static));
    }

    /**
     * Parse Argument value and call other Services or load yaml
     *
     * @return void
     **/
    protected function parseArgumentValue(&$item, &$value)
    {
        if (is_string($item) &&  substr($item, 0, 1) == "%") {
            $item = $this->get(str_replace('%', '', $item));
        }

    }

    /**
     * Get Service by Alias defined in $this->addService || $this->createService
     **/
    public function get ($alias)
    {
        if (isset($this->container[$alias])) {
            return $this->container[$alias]->getClass();
        } elseif ($alias == "ServiceContainer") {
            return $this;
        }

        throw new ServiceException('Service with alias [' . $alias . "] does not exist");
    }

    /**
     * Get Service by Namespace
     **/
    public function getByNamespace ($namespace)
    {
        if ($this->existsNamespace($namespace)) {
            return $this->namespaceContainer[$namespace]->getClass();
        } elseif ($namespace == "\Mesa\ServiceContainer\ServiceContainer") {
            return $this;
        }

        throw new ServiceException('Service with namespace [' . $namespace . '] not found');
    }

    /**
     * Add already created Service Class to Collection
     * 
     * @return [Bool] true
     **/
    public function add (Wrapper $wrapper)
    {
        $this->container[$wrapper->getAlias()] = $wrapper;
        $this->namespaceContainer[$wrapper->getNamespace()] = &$wrapper;
        return true;
    }

    /**
     * Remove Service from Collection
     * 
     * @return [Bool]  true on success
     **/
    public function remove(Wrapper $wrapper)
    {
        if ($this->exists($wrapper->getAlias())) {
            unset($this->container[$wrapper->getAlias()]);
        }

        if ($this->existsNamespace($wrapper->getNamespace())) {
            unset($this->namespaceContainer[$wrapper->getNamespace()]);
            return true;
        }
        return false;
    }

    /**
     * Check for existing service with $alias
     **/
    public function exists($alias)
    {
        return isset($this->container[$alias]);
    }

    /**
     * Misspelled method name :(, will be removed in the near future
     *
     * @deprecated
     **/
    public function exist($alias)
    {
        return $this->exists($alias);
    }

    /**
     * Check for existing service with Namespace
     **/
    public function existsNamespace($namespace)
    {
        return isset($this->namespaceContainer[$namespace]);
    }
}
