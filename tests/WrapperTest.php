<?php

namespace Mesa\ServiceContainer;


require_once __DIR__ . '/../src/Mesa/ServiceContainer/Wrapper.php';
require_once __DIR__ . '/../src/Mesa/ServiceContainer/ServiceException.php';

class WrapperTest extends \PHPUnit_Framework_TestCase
{
    protected $subject = null;

    public function setUp()
    {
        $this->subject = new Wrapper('test');
    }

    public function testSetName()
    {
        $this->assertTrue($this->subject->setName('name') instanceof Wrapper);
    }

    /**
     * @expectedException \Mesa\ServiceContainer\ServiceException
     **/
    public function testSetEmptyName()
    {
        $this->subject->setName("");
    }

    public function testGetName()
    {
        $name = "test.name";
        $this->subject->setName($name);
        $this->assertSame($name, $this->subject->getName());
    }

    public function testAddArgument()
    {
        $this->assertTrue($this->subject->addParam("name", "value") instanceof Wrapper);
    }

    public function testSetStatic()
    {
        $this->assertTrue($this->subject->setStatic(true) instanceof Wrapper);
    }

    public function testGetSameClass()
    {
        $this->subject = new Wrapper('\Mesa\ServiceContainer\DummyClass');
        $this->subject->addParam('first', 1);
        $this->subject->addParam('second', 2);
        $this->subject->addParam('third', new Wrapper('EmptyConstructor'));
        $this->subject->setStatic(true);
        $first = $this->subject->getClass();
        $second = $this->subject->getClass();
        $this->assertTrue($first === $second);
    }

    public function testGetDiffClass()
    {
        $this->subject = new Wrapper('\Mesa\ServiceContainer\DummyClass');
        $this->subject->addParam('first', 1);
        $this->subject->addParam('second', 2);
        $this->subject->addParam('third', new Wrapper('EmptyConstructor'));
        $first = $this->subject->getClass();
        $second = $this->subject->getClass();
        $this->assertTrue($first !== $second);
    }

    public function testEmptyConstructor()
    {
        $this->subject = new Wrapper('\Mesa\ServiceContainer\EmptyConstructor');
        $this->assertTrue($this->subject->getClass() instanceof EmptyConstructor);
    }

    public function testReturnedValue()
    {
        $this->subject = new Wrapper('\Mesa\ServiceContainer\EmptyConstructor');
        $value = 1234;
        $this->subject->addParam('param', $value);
        $this->assertSame(
            $this->subject->call('returnParam'),
            $value
        );

        $this->subject = new Wrapper('\Mesa\ServiceContainer\EmptyConstructor');
        $this->assertSame(
            $this->subject->call('returnParam', array('param' => $value)),
            $value
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     **/
    public function testCallMissingMethod()
    {

        $this->subject = new Wrapper('\Mesa\ServiceContainer\EmptyConstructor');
        $this->subject->call('missing');
    }

    public function testCallMethodWithoutParams()
    {

        $this->subject = new Wrapper('\Mesa\ServiceContainer\EmptyConstructor');
        $this->assertSame(
            $this->subject->call('noParam'),
            null
        );
    }

    public function testCallWrapperWithObject()
    {
        $object = new EmptyConstructor();
        $subject = new Wrapper($object);
        $this->assertSame(
            'Mesa\ServiceContainer\EmptyConstructor',
            $subject->getNamespace()
        );
    }

    public function testGetMethodArgs()
    {
        $object = new EmptyConstructor();
        $subject = new Wrapper($object);
        $this->assertSame(
            'param',
            $subject->getMethodParams('returnParam')[0]['name']
        );
    }
    public function testCallWrapperWithNamespace()
    {
        $subject = new Wrapper('Mesa\ServiceContainer\EmptyConstructor');
        $this->assertSame(
            'Mesa\ServiceContainer\EmptyConstructor',
            $subject->getNamespace()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     **/
    public function testWrapperWithNoExistingClass()
    {
        $subject = new Wrapper('Mesa\ServiceContainer\NotExisting');
        $subject->getClass();
    }
    /**
     * @expectedException \InvalidArgumentException
     **/
    public function testCallWrapperWithEmptyString()
    {
        $this->subject = new Wrapper('');
    }

    /**
     * @expectedException \InvalidArgumentException
     **/
    public function testAddEmptyParam()
    {
        $this->subject->addParam('', '');
    }

    /**
     * @expectedException \Mesa\ServiceContainer\ServiceException
     **/
    public function testGetNotExistentArgument()
    {
        $this->subject = new Wrapper('\Mesa\ServiceContainer\DummyClass');
        $this->subject->addParam('first', 1);
        $this->subject->addParam('second', 2);
        $this->subject->getClass();
    }
}

class DummyClass
{
    public function __construct($first, $second, Wrapper $third)
    {
    }
}