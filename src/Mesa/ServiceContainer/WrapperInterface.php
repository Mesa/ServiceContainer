<?php
/**
 * Created by PhpStorm.
 * User: mesa
 * Date: 14.11.13
 * Time: 22:39
 */
namespace Mesa\ServiceContainer;


/**
 * Class Wrapper
 * @package Mesa\ServiceContainer
 */
interface WrapperInterface
{
    /**
     * @return object
     */
    public function getClass();

    /**
     * @return null|string
     */
    public function getName();

    /**
     * @return array
     */
    public function getParameter();

    /**
     * @param $method
     *
     * @return array
     */
    public function getMethodParams($method);

    /**
     * @param $name
     *
     * @return mixed
     * @throws \Exception
     */
    public function getParam($name);

    /**
     * @param string $name
     *
     * @return $this
     * @throws ServiceException
     */
    public function setName($name);

    /**
     * @param $static
     *
     * @return $this
     */
    public function setStatic($static);

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addParam($name, $value = "");

    /**
     * @param string $method
     *
     * @return bool
     */
    public function hasMethod($method);

    /**
     * @return bool|string
     */
    public function getNamespace();

    /**
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function call($method, $args = array());
}