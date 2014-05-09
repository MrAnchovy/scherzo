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

/**
 * Mock service.
**/
class TestService
{
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
    function getService()
    {
        return new TestService;
    }

    /**
     * @covers  __get
     * @covers  __set
    **/
    function test_A_service_can_be_loaded()
    {
        $container = new Scherzo;
        $testService = $this->getService();
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
        $container->testService = $this->getService();
        // should throw an Exception
        $container->testService = $this->getService();
    }

    /**
     * @covers  register
     * @covers  __set
    **/
    function test_A_service_can_be_registered_and_lazy_loaded()
    {
        $container = new Scherzo;
        $container->register('testService', 'Scherzo\Test\TestService');
        $this->assertInstanceOf('Scherzo\Test\TestService', $container->testService);
    }

}
