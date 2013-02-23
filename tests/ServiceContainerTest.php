<?php

namespace Mesa\ServiceContainer;


require_once dirname(__FILE__) . '/../src/Service.php';
require_once dirname(__FILE__) . '/../src/ServiceContainer.php';
require_once dirname(__FILE__) . '/../Exception/ServiceException.php';

use Mesa\Exception\ServiceException;

class ServiceContainerTest extends \PHPUnit_Framework_TestCase
{

    protected $object;

    protected function setUp ()
    {
    }

    protected function tearDown ()
    {
    }

    protected function getServiceMock()
    {
        $mock = $this->getMock('Mesa\ServiceContainer\Service');
        return $mock;
    }

    public function testCreateService()
    {
        $subject = new ServiceContainer();
        $result = $subject->createService(
            'name',
            'namespace',
            array(
                'argument1' => 'value1'
            )
        );
        $this->assertTrue(
            $result instanceof Service,
            "ServiceContainer returned object was no instance of Mesa\ServiceContainer\Service"
        );
    }

    /**
     * @expectedException Mesa\Exception\ServiceException
     **/
    public function testCreateServiceWithoutName()
    {
        $subject = new ServiceContainer();
        $subject->createService(
            ' ',
            'namespace',
            array()
        );
    }

    /**
     * @expectedException Mesa\Exception\ServiceException
     **/
    public function testCreateServiceWithoutNameSpace()
    {
        $subject = new ServiceContainer();
        $subject->createService(
            'name',
            ' ',
            array()
        );
    }

    public function testAdd()
    {
        $subject = new ServiceContainer();
        $mock = $this->getServiceMock();
        $this->assertTrue($subject->add($mock));
    }

    public function testGet()
    {
        $subject = new ServiceContainer();
        $result = $subject->createService(
            "test.service",
            "Mesa\ServiceContainer\Service",
            array(),
            true
        );
        $subject->add($result);
        $this->assertTrue($subject->get('test.service') instanceof \Mesa\ServiceContainer\Service);
    }

    public function testGetSelf()
    {
        $subject = new ServiceContainer();
        $this->assertTrue(
            $subject->get('ServiceContainer') instanceof \Mesa\ServiceContainer\ServiceContainer
        );
    }

    /**
     * @expectedException \Mesa\Exception\ServiceException
     **/
    public function testGetNotExistingService()
    {
        $subject = new ServiceContainer();
        $subject->get('not.existing');
    }

    public function testGetByNamespace()
    {
        $subject = new ServiceContainer();
        $mock = $subject->createService(
            "test.service",
            "\Mesa\ServiceContainer\Service",
            array(),
            true
        );
        $subject->add($mock);
        $this->assertTrue(
            $subject->getByNamespace('\Mesa\ServiceContainer\Service') instanceof \Mesa\ServiceContainer\Service
        );
    }

    public function testGetSelfByNamespace ()
    {
        $subject = new ServiceContainer();
        $this->assertTrue(
            $subject->getByNamespace('\Mesa\ServiceContainer\ServiceContainer') instanceof \Mesa\ServiceContainer\ServiceContainer
        );
    }

    public function testRemove()
    {
        $subject = new ServiceContainer();
        $mock = $subject->createService(
            "test.service",
            "\Mesa\ServiceContainer\Service",
            array(),
            true
        );
        $subject->add($mock);
        $subject->remove($mock);
        $this->assertFalse($subject->exist('test.service'));
    }
}