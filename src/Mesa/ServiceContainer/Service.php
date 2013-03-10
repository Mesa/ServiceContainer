<?php

namespace Mesa\ServiceContainer;

use Mesa\Exception\ServiceException;

class Service
{

    protected $is_loaded = false;
    protected $class = null;
    protected $static = false;
    protected $className = null;
    protected $arguments = array();
    protected $name = null;

    /**
     * Define name for Service
     *
     * @param String $name
     *
     * @return \System\Service
     */
    public function setName ($name)
    {
        if (empty($name) || trim($name) == "" || !is_string($name)) {
            throw new ServiceException('Empty name for Service');
        }

        $this->name = $name;
        return $this;
    }

    /**
     * Get service name
     *
     * @return String
     **/
    public function getName ()
    {
        return $this->name;
    }

    /**
     * Add Arguments to pass it to the constructor of the defined Service
     *
     * @param string $name
     * @param mixed $value

     * @return \System\Service
     */
    public function addArgument ($name, $value)
    {
        $this->arguments[$name] = $value;
        return $this;
    }

    /**
     * Get Argument by name
     *
     * @param String $name
     *
     * @return mixed
     *
     * @throws ServiceException
     */
    protected function getArgument ($name)
    {
        if (!isset($this->arguments[$name])) {
            throw new ServiceException(
                'Requested argument [' . $name . '] for Service [' . $this->getName() . '] not found'
            );
        }

        return $this->arguments[$name];
    }

    /**
     * @param string $namespace Namespace of class
     *
     * @return $this
     */
    public function setNamespace ($namespace)
    {
        if (empty($namespace) || trim($namespace) == "") {
            throw new ServiceException('Namespace is emtpy');
        }

        $this->className = $namespace;
        return $this;
    }

    public function getNamespace()
    {
        return $this->className;
    }
    /**
     * @param bool $static should class behave like a singleton
     *
     * @return \Service
     */
    public function setStatic ($static)
    {
        $this->static = (bool) $static;
        return $this;
    }

    /**
     * Create defined Class
     *
     * @return Object New Class
     */
    protected function build ()
    {
        $classReflection = new \ReflectionClass($this->className);

        /**
         * skip scan for arguments when there is no constructor
         **/
        if (!$classReflection->hasMethod('__construct') ) {
            return $classReflection->newInstance();
        }

        $constRefl = $classReflection->getMethod('__construct');

        /**
         * skip scan for arguments when constructor got none
         **/
        if ($constRefl->getNumberOfParameters() == 0) {
            return $classReflection->newInstance();
        }

        $arguments = array();

        foreach ($constRefl->getParameters() as $arg) {
            $arguments[] = $this->getArgument($arg->name);
        }

        return $classReflection->newInstanceArgs($arguments);
    }

    /**
     * Get defined Class
     *
     * @return Object
     **/
    public function getClass ()
    {
        if ($this->static) {
            if (null == $this->class) {
                $this->class = $this->build();
            }
            return $this->class;
        } else {
            return $this->build();
        }
    }
}
