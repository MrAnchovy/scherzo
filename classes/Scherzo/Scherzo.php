<?php
/**
 * This file is part of the Scherzo PHP application framework.
 *
 * @link       http://github.com/MrAnchovy/scherzo
 * @copyright  Copyright © 2014 [MrAnchovy](http://www.mranchovy.com/)
 * @license    MIT
**/

namespace Scherzo;

use Exception, Scherzo\Core\ScherzoException;

/**
 * Scherzo dependency injection container.
 *
 * @package  Scherzo\Core
 * @version  v0.0.0-dev
**/
class Scherzo
{
    /** Version identification. */
    const VERSION = '0.0.0-dev';

    /** Loaded services. */
    protected $_loaded = array();

    /** Registered services. */
    protected $_registered = array();

    /**
     * Retrieve or lazy-load a service.
     *
     * @param   string  $name The name of the service
     * @return  object  The service.
    **/
    public function __get($name)
    {
        if (!isset($this->_loaded[$name])) {
            $this->load($name);
        }
        return $this->_loaded[$name];
    }

    /**
     * Load a service.
     *
     * @param  string  $name  The name of the service.
     * @param  object  $value The service.
    **/
    public function __set($name, $value)
    {
        if (isset($this->_loaded[$name])) {
            throw new ScherzoException(array(
                'Service ":service" cannot be overloaded',
                ':service' => $name));
        }
        $this->_loaded[$name] = $value;
    }

    /**
     * List loaded services.
     *
     * @return  array  All loaded services.
    **/
    public function getLoaded()
    {
        return $this->_loaded;
    }

    /**
     * Load defined service.
     *
     * @param  string  $name   The name the service is registered with.
     * @param  string  $alias  Optional alias to use for the service.
    **/
    public function load($name, $alias = null)
    {
        if ($alias === null) {
            $alias = $name;
        }
        if (isset($this->_loaded[$alias])) {
            throw new ScherzoException(array(
                'Cannot overlad existing service ":alias" with registered service ":service"',
                ':alias' => $name,
                ':service' => $name));
        }
        if (!isset($this->_registered[$name])) {
            throw new ScherzoException(array(
                'Cannot load unregistered service ":service"',
                ':service' => $name));
        }
        $this->_loaded[$alias] = new $this->_registered[$name]($this, $alias);
    }

    /**
     * Register a service.
     *
     * @param  string        $name    The name of the service.
     * @param  string|array  $service The class name or array defining the service.
    **/
    public function register($name, $service = null)
    {
        if (is_array($name)) {
            $this->_registered = array_merge($this->_registered, $name);
        }
        $this->_registered[$name] = $service;
    }

} // end class Scherzo
