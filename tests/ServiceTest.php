<?php

namespace Mesa\ServiceContainer;


require_once dirname(__FILE__) . '/../src/Service.php';
require_once dirname(__FILE__) . '/../Exception/ServiceException.php';

use Mesa\Exception\ServiceException;

class ServiceTest extends \PHPUnit_Framework_TestCase
{

    public function testSetName()
    {
        $subject = new Service();
        $this->assertTrue($subject->setName('name') instanceof Service);
    }

    /**
     * @expectedException \Mesa\Exception\ServiceException
     **/
    public function testSetEmptyName()
    {
        $subject = new Service();
        $subject->setName("");
    }

    public function testGetName()
    {
        $name = "test.name";
        $subject = new Service();
        $subject->setName($name);
        $this->assertSame($name, $subject->getName());
    }

    public function testAddArgument()
    {
        $subject = new Service();
        $this->assertTrue($subject->addArgument("name", "value") instanceof \Mesa\ServiceContainer\Service);
    }

    public function testSetNamespace()
    {
        $subject = new Service();
        $this->assertTrue($subject->setNamespace("namespace") instanceof \Mesa\ServiceContainer\Service);
    }

    /**
     * @expectedException \Mesa\Exception\ServiceException
     **/
    public function testEmptyNamespace()
    {
        $subject = new Service();
        $subject->setNamespace('');
    }

    public function testSetStatic()
    {
        $subject = new Service();
        $this->assertTrue($subject->setStatic(true) instanceof \Mesa\ServiceContainer\Service);
    }

    public function getSameClass()
    {
        $subject = new Service();
        $subject->getClass();
    }
}
