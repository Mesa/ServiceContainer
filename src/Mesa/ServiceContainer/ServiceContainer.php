<?php

namespace Mesa\ServiceContainer;

class ServiceContainer
{
    protected $aliasContainer = array();
    protected $namespaceContainer = array();

    /**
     * Create service class
     *
     * @param string $name
     * @param string $namespace
     * @param array  $arguments
     * @param bool   $static
     *
     * @throws ServiceException
     * @return Wrapper
     */
    protected function createService($name, $namespace, $arguments = array(), $static = false)
    {
        if (!is_object($namespace) && (trim($name) == "" || trim($namespace) == "")) {
            throw new ServiceException('Service name/namespace was empty');
        }

        $wrapper = new Wrapper($namespace);
        $wrapper->setName($name);


        $arguments = $this->prepareArguments($arguments);
        foreach ($arguments as $argName => $argValue) {
            $wrapper->addParam($argName, $argValue);
        }
        $wrapper->setStatic($static);

        return $wrapper;
    }

    protected function prepareArguments($arguments)
    {
        if (null == $arguments) {
            return array();
        }

        if (!is_array($arguments)) {
            throw new \InvalidArgumentException('Service arguments must be an array or null');
        }

        array_walk_recursive($arguments, array($this, 'parseArgumentValue'));

        return $arguments;
    }

    /**
     * Call service method and get returned Value
     *
     * @param string $name
     * @param string $method
     * @param array  $parameters
     *
     * @throws \InvalidArgumentException
     * @return Mixed
     */
    public function call($name, $method, array $parameters = array())
    {
        if ($this->exists($name)) {
            $wrapper = $this->aliasContainer[$name];
        } elseif ($this->existsNamespace($name)) {
            $wrapper = $this->namespaceContainer[$name];
        } else {
            throw new \InvalidArgumentException("No Service found with alias/namespace with " . $name);
        }

        if (!$wrapper->hasMethod($method)) {
            throw new \InvalidArgumentException(
                'Class ' . $wrapper->getNamespace() . ' has no method called ' . $method
            );
        }

        foreach ($parameters as $argName => $argValue) {
            $wrapper->addParam($argName, $argValue);
        }

        return $wrapper->call($method);
    }

    /**
     * Wrapper class for $this->createService && $this->add
     **/
    public function addService($alias, $namespace, $arguments = array(), $static = false)
    {
        return $this->add($this->createService($alias, $namespace, $arguments, $static));
    }

    /**
     * Parse argument value and call other service
     *
     * @param string $item
     * @param        $value
     *
     * @return void
     */
    protected function parseArgumentValue(&$item, &$value)
    {
        if (is_string($item) && substr($item, 0, 1) == "%") {
            $item = $this->get(str_replace('%', '', $item));
        }
    }

    /**
     * Get service by name defined in $this->addService || $this->createService
     *
     * @param string $name
     **/
    public function get($name)
    {
        if (isset($this->aliasContainer[$name])) {
            return $this->aliasContainer[$name]->getClass();
        } elseif ($name == "ServiceContainer") {
            return $this;
        }

        throw new ServiceException('Service with alias [' . $name . "] does not exist");
    }

    /**
     * Get service by namespace
     *
     * @param string $namespace
     *
     * @throws ServiceException
     * @return object
     */
    public function getByNamespace($namespace)
    {
        if ($this->existsNamespace($namespace)) {
            return $this->namespaceContainer[$namespace]->getClass();
        } elseif ($namespace == '\\' . get_class($this) || $namespace == "ServiceContainer") {
            return $this;
        }

        throw new ServiceException('Service with namespace [' . $namespace . '] not found');
    }

    /**
     * Add already created Service Class to Collection
     *
     * @param Wrapper $wrapper
     * @param Wrapper $wrapper
     *
     * @return bool true
     */
    protected function add(Wrapper $wrapper)
    {
        $this->aliasContainer[$wrapper->getName()]          = $wrapper;
        $this->namespaceContainer[$wrapper->getNamespace()] = & $wrapper;

        return true;
    }

    /**
     * Remove service from collection
     *
     * @param object $class
     *
     * @return void
     */
    public function remove($class)
    {

        $reflection = new \ReflectionObject($class);
        $namespace  = $reflection->getName();
        $wrapper    = $this->namespaceContainer[$namespace];
        unset($this->aliasContainer[$wrapper->getName()]);
        unset($this->namespaceContainer[$wrapper->getNamespace()]);
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function exists($name)
    {
        return isset($this->aliasContainer[$name]);
    }

    /**
     * Check for existing service with namespace
     *
     * @param string $namespace
     *
     * @return bool
     **/
    public function existsNamespace($namespace)
    {
        return isset($this->namespaceContainer[$namespace]);
    }
}
