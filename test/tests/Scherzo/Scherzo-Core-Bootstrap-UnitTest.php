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
use Scherzo\Core\Bootstrap;

// include the class file
require_once __DIR__.'/../../../classes/Scherzo/Core/Bootstrap.php';

/**
 * Unit tests for Scherzo\Core\Bootstrap class.
**/
class Scherzo_Core_Bootstrap_UnitTest extends \PHPUnit_Framework_Testcase
{

    /**
     * @covers  bootstrap
    **/  
    function test_Bootstrap_error_message()
    {
        $options = new \StdClass;
        $options->unittest = true;
        $this->expectOutputRegex('@^This site is temporarily closed@');
        Bootstrap::bootstrap($options);
    }

    /**
     * @covers  bootstrap
    **/  
    function test_Bootstrap_non_production_error_message()
    {
        $options = new \StdClass;
        $options->unittest = true;
        $options->deployment = 'coreTest';
        $this->expectOutputRegex('@^Scherzo bootstrap error@');
        Bootstrap::bootstrap($options);
    }
}
