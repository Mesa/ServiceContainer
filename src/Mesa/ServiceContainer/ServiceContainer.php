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

        $this->addParameters($wrapper, $arguments);
        $wrapper->setStatic($static);

        return $wrapper;
    }

    protected function prepareWrapper(WrapperInterface $wrapper)
    {

        $parameters = $this->prepareArguments(
            $wrapper->getParameter()
        );
        $this->addParameters($wrapper, $parameters);
    }

    protected function prepareArguments(array $arguments)
    {
        if (null == $arguments || count($arguments) == 0) {
            return array();
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
        $this->addParameters($wrapper, $parameters);
        $this->prepareWrapper($wrapper);

        return $wrapper->call($method);
    }

    /**
     * @param \Mesa\ServiceContainer\WrapperInterface $wrapper
     * @param array                                                                  $parameters
     *
     * @return bool
     */
    protected function addParameters(WrapperInterface $wrapper, $parameters)
    {
        if (!is_array($parameters) || count($parameters) == 0) {
            return false;
        }

        foreach ($parameters as $name => $value) {
            if (!empty($name)) {
                $wrapper->addParam($name, $value);
            }
        }
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
     * @param mixed  $value
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
     * Get service by name defined in $this->addService
     *
     * @param string $name
     *
     * @throws ServiceException
     * @return $this
     */
    public function get($name)
    {
        if (isset($this->aliasContainer[$name])) {
            $this->prepareWrapper($this->aliasContainer[$name]);

            return $this->aliasContainer[$name]->getClass();
        }

        throw new ServiceException('Service with name [' . $name . "] not found.");
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
            $this->prepareWrapper($this->namespaceContainer[$namespace]);

            return $this->namespaceContainer[$namespace]->getClass();
        }

        throw new ServiceException('Service with namespace [' . $namespace . '] not found.');
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
