<?php
/**
 * This file is part of the Scherzo PHP application framework.
 *
 * @link       http://github.com/MrAnchovy/scherzo
 * @copyright  Copyright © 2014 [MrAnchovy](http://www.mranchovy.com/)
 * @license    MIT
**/

namespace Scherzo\Core;

use Exception, Scherzo\Core\ScherzoException;

/**
 * Optional foundation for a Scherzo service.
 *
 * Most services in Scherzo\Core extend this class purely for convenience.
 *
 * @package  Scherzo\Core
**/
abstract class Service
{
    /**
     * The dependency injection container.
     *
     * @var  Scherzo\Scherzo  The dependency injection container.
    **/
    protected $depends;

    /**
     * The name the service is registered in the container as.
     *
     * @var  string
    **/
    protected $name;

    /**
     * Final constructor.
     *
     * This is made final to avoid omission of a call to `parent::__construct()`
     * in implementing classes. See `beforeConstruct()` and `afterConstruct()`.
     *
     * @var  Scherzo\Scherzo  The dependency injection container.
    **/
    final public function __construct($depends, $name)
    {
        $this->depends = $depends;
        $this->name = $name;
        $this->afterConstruct();
    }

    /**
     * Hook to constructor for implementing classes.
    **/
    protected function afterConstruct()
    {
    }
}
