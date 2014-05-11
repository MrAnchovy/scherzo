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
 * Base class for Scherzo controllers.
 *
 * Extending this base class is not mandatory, but it makes life a whole lot easier.
**/
abstract class Controller {

    /**
     * Routes to use with 0, 1, 2 or more parts in the path.
     *
     * - The first and second elements are only relevant for the DefaultController
     *   which is used for paths like `/` and `/part1`.
     * - The third element is used for `/controller/part2` (part1 is always mapped to
     *   the controller).
     * - The fourth element is used for `/controller/part2/part3...`.
     *
     * **CAUTION** the elements of this array are used to set properties of this
     * object without further validation.
    **/
    protected $routes = array(
        array(),
        array('action'),
        array('controller', 'action'),
        array('controller', 'id', 'action'),
    );

    protected $apis = array(
        'json'  => 'Scherzo\ResponseJson',
        // 'xml'   => 'Scherzo\ResponseXml',
        // 'yaml'  => 'Scherzo\ResponseYaml',
        // 'text'  => 'Scherzo\ResponseText',
    );

    protected $depends;

    protected $route;
    protected $action;
    protected $id;

    final public function __construct($depends)
    {
        $this->depends = $depends;
        $this->afterConstruct();
    }

    protected function afterConstruct()
    {
    }

    /**
     * Hook run immediately before the requested action.
    **/
    protected function before()
    {
    }

    /**
     * Hook run immediately after the requested action.
    **/
    protected function after()
    {
    }

    /**
     * Set the action and id from the request path.
     *
     * The path has already been parsed as follows 
     * $this->route = array(
     *     'controller' => 'name',
     *     'parts'      => array(part1, part2, part3...),
     *     'extension'  => 'json',
     * );
    **/
    protected function setRoute()
    {
        $parts = $this->route['parts'];

        $map = $this->routes[count($parts)];

        foreach ($map as $key=>$value) {
            $this->$value = $parts[$key];
            if ($this->$value === '') {
                $this->$value = null;
            }
        }
    }

    public function execute($route) {

        $this->route = $route;

        $actionMethod = 'action' . ucfirst(strtolower($this->depends->request->method));

        $rc = new \ReflectionClass($this);

        // set the id and action from the route
        $this->setRoute();

        if ($this->action === null) {
            // try actionGet($id)
            $done = $this->executeMethod($rc, $actionMethod, $this->id);
        } else {
            // try actionGet_action($id)
            $done = $this->executeMethod($rc, "{$actionMethod}_$this->action", $this->id);
        }

        if (!$done) {
            $class = get_class($this);
            throw new ScherzoException(404);
        }

        if (is_object($this->response)) {
            $this->response->send();
        } else {
            echo $this->response;
        }
    }

    protected function executeMethod($rc, $method, $id = null) {
        // check the action for case sensitivity
        if ($rc->hasMethod($method) && $rc->getMethod($method)->getName() === $method) {
            $this->method = $method;
            $this->before();
            $this->response = $this->$method($id);
            $this->after();
            return true;
        } else {
            return false;
        }
    }
}
