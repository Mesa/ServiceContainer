<?php

namespace Mesa\ServiceContainer;


require_once dirname(__FILE__) . '/../src/Mesa/ServiceContainer/Wrapper.php';
require_once dirname(__FILE__) . '/../src/Mesa/ServiceContainer/ServiceContainer.php';
require_once dirname(__FILE__) . '/../src/Mesa/ServiceContainer/ServiceException.php';

class ServiceContainerTest extends \PHPUnit_Framework_TestCase
{

    protected $object;

    protected function getServiceMock()
    {
        $mock = new Wrapper('Mesa\ServiceContainer\Wrapper');
        return $mock;
    }

    public function testReferencingService()
    {
        $subject = new ServiceContainer();
        $subject->addService(
            'test.service',
            'Mesa\ServiceContainer\Testing'
        );

        $result = $subject->addService(
            "ReferencingClass",
            "Mesa\ServiceContainer\ReferencingClass",
            array(
                'testing' => '%test.service%'
            ),
            false
        );
    }

    /**
     * @expectedException Mesa\ServiceContainer\ServiceException
     **/
    public function testMissingReference()
    {
        $subject = new ServiceContainer();
        $subject->addService(
            'ref.service',
            'Mesa\ServiceContainer\ReferencingClass',
            array(
                'missing.Service',
                '%notThere%'
            )
        );
    }


    /**
     * @expectedException \InvalidArgumentException
     **/
    public function testCallBackMissingServce()
    {
        $subject = new ServiceContainer();
        $subject->addService(
            "test.service",
            "Mesa\ServiceContainer\EmptyConstructor",
            array(),
            true
        );

        $value = 12345;
        $result = $subject->call(
            'noExisting',
            'missingMethod',
            array('param' => $value)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     **/
    public function testCallBackMissingMethod()
    {
        $subject = new ServiceContainer();
        $subject->addService(
            "test.service",
            "Mesa\ServiceContainer\EmptyConstructor",
            array(),
            true
        );

        $value = 12345;
        $result = $subject->call(
            "Mesa\ServiceContainer\EmptyConstructor",
            'missingMethod',
            array('param' => $value)
        );

    }

    public function testCallBackWithNamespace()
    {
        $subject = new ServiceContainer();
        $subject->addService(
            "test.service",
            "Mesa\ServiceContainer\EmptyConstructor",
            array(),
            true
        );

        $value = 12345;
        $result = $subject->call(
            "Mesa\ServiceContainer\EmptyConstructor",
            'returnParam',
            array('param' => $value)
        );

        $this->assertSame($value, $result);
    }

    public function testCallBackWithAlias()
    {
        $subject = new ServiceContainer();
        $subject->addService(
            "test.service",
            "Mesa\ServiceContainer\EmptyConstructor",
            array(),
            true
        );

        $value = 12345;
        $result = $subject->call(
            'test.service',
            'returnParam',
            array('param' => $value)
        );

        $this->assertSame($value, $result);
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
            $result instanceof Wrapper,
            "ServiceContainer returned object was no instance of Mesa\ServiceContainer\Wrapper"
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

    public function testAddObject()
    {
        $subject = new ServiceContainer();
        $this->assertTrue($subject->addService('test.service', new EmptyConstructor()));
    }
    public function testGet()
    {
        $subject = new ServiceContainer();
        $result = $subject->createService(
            "test.service",
            "Mesa\ServiceContainer\EmptyConstructor",
            array(),
            true
        );
        $subject->add($result);
        $obj1 = $subject->get('test.service');
        $obj2 = $subject->get('test.service');
        $this->assertTrue($subject->get('test.service') instanceof \Mesa\ServiceContainer\EmptyConstructor);
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
        $subject->addService(
            "test.service",
            "\Mesa\ServiceContainer\EmptyConstructor",
            array(),
            true
        );
        $this->assertTrue(
            $subject->getByNamespace(
                '\Mesa\ServiceContainer\EmptyConstructor'
            ) instanceof \Mesa\ServiceContainer\EmptyConstructor
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
            $subject->getByNamespace(
                '\Mesa\ServiceContainer\ServiceContainer'
            ) instanceof \Mesa\ServiceContainer\ServiceContainer
        );
    }

    public function testGetStaticService()
    {
        $subject = new ServiceContainer();
        $subject->add(
            $subject->createService(
                'testService',
                '\Mesa\ServiceContainer\Testing',
                null,
                true
            )
        );
        $test1 = $subject->get('testService');
        $test1->value = 10;
        $test2 = $subject->get('testService');

        $this->assertSame(
            $test1,
            $test2
        );
    }

    public function testRemove()
    {
        $subject = new ServiceContainer();
        $mock = $subject->createService(
            "test.service",
            "\Mesa\ServiceContainer\EmptyConstructor",
            array(),
            true
        );
        $subject->add($mock);
        $this->assertTrue($subject->remove($mock));
        $this->assertFalse($subject->exist('test.service'));
    }

    public function testNotExistingService()
    {
        $subject = new ServiceContainer();
        $mock = $subject->createService(
            "test.service",
            "\Mesa\ServiceContainer\Service",
            array(),
            true
        );
        $this->assertFalse($subject->remove($mock));
    }
}


class Testing
{
    public $value = 0;
}

class ReferencingClass
{
    public function __construct(Testing $testing)
    {

    }
    public function callback()
    {
        return 1234;
    }
}
