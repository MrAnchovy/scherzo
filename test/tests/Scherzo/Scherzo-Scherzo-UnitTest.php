<?php
/**
 * This file is part of the Scherzo PHP application framework.
 *
 * @link       http://github.com/MrAnchovy/scherzo
 * @copyright  Copyright © 2014 [MrAnchovy](http://www.mranchovy.com/)
 * @license    MIT
**/

namespace Scherzo\Test;

use Exception;

// the class being tested
use Scherzo\Scherzo;

// include the class file
require_once __DIR__.'/../../../classes/Scherzo/Scherzo.php';

// include requirements
require_once __DIR__.'/../../../classes/Scherzo/Core/ScherzoException.php';
require_once __DIR__.'/../../../classes/Scherzo/Core/Service.php';

/**
 * Mock service.
**/
class TestService extends \Scherzo\Core\Service
{
    /**
     * Make sure we know who we are.
    **/
    public function getName()
    {
        return $this->name;
    }
}

/**
 * Unit tests for \Scherzo\Scherzo class.
**/
class Scherzo_Scherzo_UnitTest extends \PHPUnit_Framework_Testcase
{

    /**
     * @covers  __get
     * @covers  __set
    **/
    function test_A_service_can_be_loaded()
    {
        $container = new Scherzo;
        $testService = new TestService(null, null);
        $container->testService = $testService;
        $this->assertSame($testService, $container->testService);
    }

    /**
     * @expectedException         Exception
     * @expectedExceptionMessage  Service "testService" cannot be overloaded
     *
     * @covers  __get
     * @covers  __set
    **/  
    function test_An_existing_service_cannot_be_overloaded()
    {
        $container = new Scherzo;
        $container->testService = new TestService(null, null);
        // should throw an Exception
        $container->testService = new TestService(null, null);
    }

    /**
     * @covers  register
     * @covers  __set
    **/
    function test_A_service_can_be_registered_and_lazy_loaded()
    {
        $container = new Scherzo;
        $container->register('testService', __NAMESPACE__.'\TestService');
        $this->assertInstanceOf('Scherzo\Test\TestService', $container->testService);
    }

    /**
     * @covers  register
     * @covers  load
     * @covers  __set
    **/
    function test_A_service_can_be_loaded_with_an_optional_alias()
    {
        // set up
        $container = new Scherzo;
        $container->register('testService', __NAMESPACE__.'\TestService');

        // without an alias
        $container->load('testService');
        $this->assertSame('testService', $container->testService->getName());

        // with an alias
        $container->load('testService', 'testAlias');
        $this->assertSame('testAlias', $container->testAlias->getName());
    }
}
