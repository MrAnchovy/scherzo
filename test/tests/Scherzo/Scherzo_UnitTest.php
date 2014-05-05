<?php
/**
 * Tests for \Scherzo\Scherzo class.
 *
 * @copyright  Copyright © 2014 MrAnchovy http://www.mranchovy.com/
 * @license    MIT
**/

namespace Scherzo;

use Exception;

// include the class file
require_once __DIR__.'/../../../classes/Scherzo/Scherzo.php';

// create a simple service for testing
class TestService
{
}

class UnitTest_Scherzo extends \PHPUnit_Framework_Testcase
{

    function getService()
    {
        return new TestService;
    }

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
    **/  
    function test_An_existing_service_cannot_be_overloaded()
    {
        $container = new Scherzo;
        $container->testService = $this->getService();
        $container->testService = $this->getService();
    }

    function test_A_service_can_be_defined_and_lazy_loaded()
    {
        $container = new Scherzo;
        $container->define('testService', 'Scherzo\TestService');
        $this->assertInstanceOf('Scherzo\TestService', $container->testService);
    }

}
