<?php

namespace Mesa\ServiceContainer;

/**
 * Class Wrapper
 * @package Mesa\ServiceContainer
 */
class Wrapper implements WrapperInterface
{
    protected $object = null;
    protected $namespace = false;
    protected $parameter = array();
    protected $reflection = false;
    protected $name = "";
    protected $static = false;

    /**
     * @param object|string $class
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($class)
    {
        if (empty($class) || (!is_string($class) && !is_object($class))) {
            throw new \InvalidArgumentException('Type is not supported. Please use Namespace or Object');
        }

        if (is_string($class)) {
            $this->namespace = $class;
        }

        if (is_object($class)) {
            $this->object     = $class;
            $this->reflection = new \ReflectionObject($class);
            $this->namespace  = $this->reflection->getName();
        }
    }

    /**
     * @param $static
     *
     * @return $this
     */
    public function setStatic($static)
    {
        $this->static = (bool)$static;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     * @throws ServiceException
     */
    public function setName($name)
    {
        if (empty($name)) {
            throw new ServiceException('Name of ' . $this->getNamespace() . 'was empty');
        }
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool|string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return array
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function call($method, $args = array())
    {
        $class = $this->getClass();

        foreach ($args as $key => $value) {
            $this->addParam($key, $value);
        }

        if (!$this->hasMethod($method)) {
            throw new \InvalidArgumentException(
                'Class [' . $this->reflection->getNamespaceName() . '] has no Method [' . $method . ']'
            );
        }

        $reflection = new \ReflectionMethod($class, $method);
        $args       = $this->prepareArgs($reflection);

        if (!$args) {
            return $reflection->invoke($class);
        }

        if ($this->static === true) {
            $this->object = $class;
        }

        return $reflection->invokeArgs($class, $args);
    }

    /**
     * @return object
     */
    public function getClass()
    {
        if (!$this->static) {
            return $this->createClass();
        }

        if (!$this->object) {
            $this->object = $this->createClass();
        }

        return $this->object;
    }

    /**
     * @return object
     * @throws \InvalidArgumentException
     */
    protected function createClass()
    {
        try {
            $this->reflection = new \ReflectionClass($this->namespace);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }

        if (!$this->reflection->hasMethod("__construct")) {
            return $this->reflection->newInstance();
        }

        $method = $this->reflection->getMethod('__construct');
        $args   = $this->prepareArgs($method);

        if (!$args) {
            return $this->reflection->newInstance();
        }

        return $this->reflection->newInstanceArgs($args);
    }

    /**
     * @param \ReflectionMethod $method
     *
     * @return array|bool
     * @throws ServiceException
     */
    protected function prepareArgs(\ReflectionMethod $method)
    {
        if ($method->getNumberOfParameters() == 0) {
            return false;
        }

        $parameter = array();
        foreach ($this->getArgs($method) as $arg) {
            try {
                $parameter[] = $this->getParam($arg->getName());
            } catch (\Exception $e) {
                if (!$arg->isOptional()) {
                    throw new ServiceException(
                        'Parameter [' . $arg->getName() . '] for Class [' . $this->namespace . '] not found'
                    );
                }
            }
        }

        return $parameter;
    }

    /**
     * @param \ReflectionMethod $method
     *
     * @return array
     */
    protected function getArgs(\ReflectionMethod $method)
    {
        if ($method->getNumberOfParameters() == 0) {
            return array();
        }
        $parameter = array();
        foreach ($method->getParameters() as $arg) {
            $parameter[] = $arg;
        }

        return $parameter;
    }

    /**
     * @param $name
     *
     * @return mixed
     * @throws \Exception
     */
    public function getParam($name)
    {
        if (!isset($this->parameter[$name])) {
            throw new \Exception(
                'Parameter [' . $name . '] for Class [' . $this->namespace . '] not found'
            );
        }

        return $this->parameter[$name];
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addParam($name, $value = "")
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Empty argument name');
        }
        $this->parameter[$name] = $value;

        return $this;
    }

    /**
     * @param string $method
     *
     * @return bool
     */
    public function hasMethod($method)
    {
        if ($this->object == null) {
            $this->object = $this->createClass();
        }

        return $this->reflection->hasMethod($method);
    }

    /**
     * @param $method
     *
     * @return array
     */
    public function getMethodParams($method)
    {
        $reflection = new \ReflectionMethod($this->namespace, $method);
        $parameter  = array();
        foreach ($this->getArgs($reflection) as $arg) {
            $parameter[] = array(
                'name'      => $arg->name,
                'reflParam' => $arg
            );
        }

        return $parameter;
    }
}
