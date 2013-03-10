<?php

namespace Mesa\ServiceContainer;


require_once __DIR__ . '/DummyClass.php';
require_once __DIR__ . '/../src/Mesa/ServiceContainer/Service.php';
require_once __DIR__ . '/../src/Mesa/ServiceContainer/ServiceException.php';

class ServiceTest extends \PHPUnit_Framework_TestCase
{

    public function testSetName()
    {
        $subject = new Service();
        $this->assertTrue($subject->setName('name') instanceof Service);
    }

    /**
     * @expectedException \Mesa\ServiceContainer\ServiceException
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
     * @expectedException \Mesa\ServiceContainer\ServiceException
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

    public function testGetSameClass()
    {
        $subject = new Service();
        $subject->setNamespace('\Mesa\ServiceContainer\DummyClass');
        $subject->addArgument('first', 1);
        $subject->addArgument('second', 2);
        $subject->addArgument('third', new Service());
        $subject->setStatic(true);
        $first = $subject->getClass();
        $second = $subject->getClass();
        $this->assertSame($first, $second);
    }

    public function testGetDiffClass()
    {
        $subject = new Service();
        $subject->setNamespace('\Mesa\ServiceContainer\DummyClass');
        $subject->addArgument('first', 1);
        $subject->addArgument('second', 2);
        $subject->addArgument('third', new Service());
        $first = $subject->getClass();
        $second = $subject->getClass();
        $this->assertTrue($first !== $second);
    }

    public function testEmptyConstructor()
    {
        $subject = new Service();
        $subject->setNamespace('\Mesa\ServiceContainer\EmptyConstructor');
        $this->assertTrue($subject->getClass() instanceof \Mesa\ServiceContainer\EmptyConstructor);
    }

    /**
     * @expectedException \Mesa\ServiceContainer\ServiceException
     **/
    public function testGetNotExistentArgument()
    {
        $subject = new Service();
        $subject->setNamespace('\Mesa\ServiceContainer\DummyClass');
        $subject->addArgument('first', 1);
        $subject->addArgument('second', 2);
        $first = $subject->getClass();
    }
}
