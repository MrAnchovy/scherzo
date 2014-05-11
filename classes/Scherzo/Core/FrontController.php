<?php

/*
Would a decorator pattern be useful for Request (and Response?)

TODO refactor routing - routing should be a service and the resulting route
should belong to the request

*/

namespace Scherzo\Core;

use Exception, Scherzo\Core\ScherzoException;

use ReflectionClass;

class FrontController extends \Scherzo\Core\Service
{
    public function execute() {

        // get the request service
        if (PHP_SAPI === 'cli') {
            $this->depends->load('cliRequest', 'request');
        } else {
            $this->depends->load('httpRequest', 'request');
        }

        // $log = $this->depends->log->setId($request->id);
        // $request->log($log);

        // strip the base url from the beginning of the path
        $path = substr($this->depends->request->path, strlen($this->depends->local->coreBaseUrl));

        // $route = $this->depends->route->parse($path);
        $route = $this->getRoute($path);

        // get the controller class
        // print_r($route);
        // return;
        $appns = $this->depends->local->coreApplicationNamespace;
        $controller = $route['controller'] === null
            ? "$appns\\Controllers\\DefaultController"
            : "$appns\\Controllers\\Controller_$route[controller]";

        try {
            // check the case matches (PHP class names are case insensitive)
            $class = new ReflectionClass($controller);
            if ($class->getName() === $controller) {
                $success = true;
            }
        } catch (Exception $e) {
        }
        if (empty($success)) {
            $message = 'I can\'t find :c';
            $vars = array(':c' => htmlspecialchars($route['controller']));
            $controller = new $this->depends->local->coreErrorController($this->depends);
            $controller->execute_404($route);
        } else {
            $controller = new $controller($this->depends);

            $controller->execute($route);
        }
    }

    protected function getRoute($path)
    {
        $route = array();
        $parts = explode('/', $path);
        $count = count($parts);
        if ($count < 2) {
            $route['controller'] = null;
        } else {
            $route['controller'] = $parts[0];
        }
        if ($count > 0) {
            $last = $parts[$count - 1];
            if (($pos = strrpos($last, '.')) !== false) {
                $parts[$count - 1] = substr($last, 0, $pos);
                $route['extension'] = substr($last, $pos + 1);
            } else {
                $route['extension'] = null;
            }
        }
        $route['parts'] = $parts;
        return $route;
    }

}
