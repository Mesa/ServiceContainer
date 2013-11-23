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
     * Wrapper name for $this->createService && $this->add
     **/
    public function addService($alias, $namespace, $arguments = array(), $static = false);

    /**
     * Remove service from collection
     *
     * @param object $name
     *
     * @return void
     */
    public function remove($name);

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