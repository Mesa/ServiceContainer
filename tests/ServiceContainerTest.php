<?php

namespace Mesa\ServiceContainer;


require_once dirname(__FILE__) . '/../src/Mesa/ServiceContainer/Service.php';
require_once dirname(__FILE__) . '/../src/Mesa/ServiceContainer/ServiceContainer.php';
require_once dirname(__FILE__) . '/../src/Mesa/ServiceContainer/ServiceException.php';

class ServiceContainerTest extends \PHPUnit_Framework_TestCase
{

    protected $object;

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
     * @expectedException \Mesa\ServiceContainer\ServiceException
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
     * @expectedException \Mesa\ServiceContainer\ServiceException
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
        $obj1 = $subject->get('test.service');
        $obj2 = $subject->get('test.service');
        $this->assertTrue($subject->get('test.service') instanceof \Mesa\ServiceContainer\Service);
        $this->assertSame($obj1, $obj2);
    }

    public function testGetSelf()
    {
        $subject = new ServiceContainer();
        $this->assertTrue(
            $subject->get('ServiceContainer') instanceof \Mesa\ServiceContainer\ServiceContainer
        );
    }

    /**
     * @expectedException \Mesa\ServiceContainer\ServiceException
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

    /**
     * @expectedException \Mesa\ServiceContainer\ServiceException
     **/
    public function testGetNotExistingByNamespace()
    {
        $subject = new ServiceContainer();
        $mock = $subject->createService(
            "test.service",
            "\Mesa\ServiceContainer\Service",
            array(),
            true
        );
        $subject->add($mock);
        $subject->getByNamespace('\Fake\Namespace');
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
