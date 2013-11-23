<?php

namespace Mesa\ServiceContainer;

interface ServiceContainerInterface
{
    /**
     * @param $name
     *
     * @return bool
     */
    public function exists($name);

    /**
     * Wrapper class for $this->createService && $this->add
     **/
    public function addService($alias, $namespace, $arguments = array(), $static = false);

    /**
     * Check for existing service with namespace
     *
     * @param string $namespace
     *
     * @return bool
     **/
    public function existsNamespace($namespace);

    /**
     * Get service by namespace
     *
     * @param string $namespace
     *
     * @throws ServiceException
     * @return object
     */
    public function getByNamespace($namespace);

    /**
     * Remove service from collection
     *
     * @param object $class
     *
     * @return void
     */
    public function remove($class);

    /**
     * Get service by name defined in $this->addService
     *
     * @param string $name
     *
     * @throws ServiceException
     * @return $this
     */
    public function get($name);

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
    public function call($name, $method, array $parameters = array());
}