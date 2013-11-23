<?php

namespace Mesa\ServiceContainer;


require_once dirname(__FILE__) . '/../src/Mesa/ServiceContainer/WrapperInterface.php';
require_once dirname(__FILE__) . '/../src/Mesa/ServiceContainer/Wrapper.php';
require_once dirname(__FILE__) . '/../src/Mesa/ServiceContainer/ServiceContainerInterface.php';
require_once dirname(__FILE__) . '/../src/Mesa/ServiceContainer/ServiceContainer.php';
require_once dirname(__FILE__) . '/../src/Mesa/ServiceContainer/ServiceException.php';

class ServiceContainerTest extends \PHPUnit_Framework_TestCase
{

    protected $object;

    public function testReferencingService()
    {
        $subject = new ServiceContainer();

        $subject->addService(
            "ReferencingClass",
            'Mesa\ServiceContainer\ReferencingClass',
            array(
                'testing' => '%test.service%'
            ),
            false
        );

        $subject->addService(
            'test.service',
            'Mesa\ServiceContainer\Testing'
        );

        $this->assertTrue(
            $subject->get("ReferencingClass") instanceof ReferencingClass
        );
    }

    /**
     * @expectedException \Mesa\ServiceContainer\ServiceException
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
        $subject->get("ref.service");
    }

    /**
     * @expectedException \InvalidArgumentException
     **/
    public function testCallBackMissingService()
    {
        $subject = new ServiceContainer();
        $subject->addService(
            "test.service",
            'Mesa\ServiceContainer\EmptyConstructor',
            array(),
            true
        );

        $value = 12345;
        $subject->call(
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
            'Mesa\ServiceContainer\EmptyConstructor',
            array(),
            true
        );

        $value = 12345;
        $subject->call(
            'test.service',
            'missingMethod',
            array('param' => $value)
        );

    }

    public function testCallBackWithNamespace()
    {
        $subject = new ServiceContainer();
        $subject->addService(
            "test.service",
            'Mesa\ServiceContainer\EmptyConstructor',
            array(),
            true
        );

        $value  = 12345;
        $result = $subject->call(
            'test.service',
            'returnParam',
            array('param' => $value)
        );

        $this->assertSame($value, $result);
    }

    public function testCallBackWithName()
    {
        $subject = new ServiceContainer();
        $subject->addService(
            "test.service",
            'Mesa\ServiceContainer\EmptyConstructor',
            array(),
            true
        );

        $value  = 12345;
        $result = $subject->call(
            'test.service',
            'returnParam',
            array('param' => $value)
        );

        $this->assertSame($value, $result);
    }

    /**
     * @expectedException \Mesa\ServiceContainer\ServiceException
     **/
    public function testAddServiceWithoutName()
    {
        $subject = new ServiceContainer();
        $subject->addService(
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
        $subject->addService(
            'name',
            ' ',
            array()
        );
    }

    public function testAddObject()
    {
        $subject = new ServiceContainer();
        $this->assertTrue($subject->addService('test.service', new EmptyConstructor()));
    }

    public function testGet()
    {
        $subject = new ServiceContainer();
        $subject->addService(
            'test.service',
            'Mesa\ServiceContainer\EmptyConstructor',
            array(),
            true
        );
        $obj1 = $subject->get('test.service');
        $obj2 = $subject->get('test.service');
        $this->assertTrue($subject->get('test.service') instanceof EmptyConstructor);
        $this->assertSame($obj1, $obj2);
    }

    /**
     * @expectedException \Mesa\ServiceContainer\ServiceException
     **/
    public function testGetNotExistingService()
    {
        $subject = new ServiceContainer();
        $subject->get('not.existing');
    }

    public function testGetStaticService()
    {
        $subject = new ServiceContainer();

        $subject->addService(
            'testService',
            '\Mesa\ServiceContainer\Testing',
            array(),
            true
        );
        $test1        = $subject->get('testService');
        $test1->value = 10;
        $test2        = $subject->get('testService');

        $this->assertSame(
            $test1,
            $test2
        );
    }

    public function testRemove()
    {
        $subject = new ServiceContainer();
        $subject->addService(
            'test.service',
            'Mesa\ServiceContainer\Testing',
            array(),
            true
        );
        $this->assertTrue($subject->exists('test.service'));
        $this->assertTrue($subject->remove('test.service'));
        $this->assertFalse($subject->exists('test.service'));
    }

    public function testRemoveNotExistingService()
    {
        $subject = new ServiceContainer();
        $this->assertFalse($subject->remove("missing.service"));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNotExistingService()
    {
        $subject = new ServiceContainer();
        $this->assertTrue(
            $subject->addService(
                'test.service',
                '\Mesa\ServiceContainer\Service',
                array(),
                true
            )
        );
        $subject->get("test.service");
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

class EmptyConstructor
{
    public function __construct()
    {
    }

    public function noParam()
    {
    }

    public function returnParam($param)
    {
        return $param;
    }
}
