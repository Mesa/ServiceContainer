<?php

namespace Mesa\ServiceContainer;

class ServiceContainer implements ServiceContainerInterface
{
    protected $container = array();

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
            $wrapper = $this->container[$name];
        } else {
            throw new \InvalidArgumentException("No Service found with alias [" . $name . "]");
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
     * @param $name
     *
     * @return bool
     */
    public function exists($name)
    {
        return isset($this->container[(string)$name]);
    }

    /**
     * @param WrapperInterface $wrapper
     * @param array            $parameters
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

        return true;
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
     * Wrapper name for $this->createService && $this->add
     **/
    public function addService($alias, $namespace, $arguments = array(), $static = false)
    {
        return $this->add($this->createService($alias, $namespace, $arguments, $static));
    }

    /**
     * Add already created Service Class to Collection
     *
     * @param WrapperInterface $wrapper
     *
     * @return bool true
     */
    protected function add(WrapperInterface $wrapper)
    {
        $this->container[$wrapper->getName()] = $wrapper;

        return true;
    }

    /**
     * Create service name
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

    /**
     * Remove service from collection
     *
     * @param string $name
     *
     * @return bool
     */
    public function remove($name)
    {
        if (!$this->exists($name)) {
            return false;
        }
        unset($this->container[$name]);

        return true;
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
        if (isset($this->container[$name])) {
            $this->prepareWrapper($this->container[$name]);

            return $this->container[$name]->getClass();
        }

        throw new ServiceException('Service with name [' . $name . "] not found.");
    }
}
