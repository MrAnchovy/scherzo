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
        $path = substr($this->depends->request->path, strlen($this->depends->local->coreBaseUrl) - 1);

        // $route = $this->depends->route->parse($path);
        $route = $this->parseRoute($path);

        // get the controller class
        // print_r($route);
        // return;
        $appns = $this->depends->local->applicationNamespace;
        $controller = $route['controller'] === null
            ? "$appns\\Controller\\DefaultController"
            : "$appns\\Controller\\Controller_$route[controller]";

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
            $controller = new $this->depends->local->coreErrorController;
            $controller->execute(404);
        }

        $controller = new $controller($this->depends);

        $controller->execute($route);
    }

    /**
     * extract the controller from the route
     *
     * @param  Request
    **/
    public function parseRoute($path)
    {

        //          @^                                           ($)@U   match start and end, ungreedy
        //            (?:(.*)                    )                       match the controller, ignoring the wrapper       [1]
        //                   (?:/(.*)          )?                        match the id if any, ignoring the wrapper        [2]
        //                           (?:/(.*))?                          match the rest if any, ignoring the wrapper      [3]
        //                                        (?:\.([^/.]+))?        match the extension if any, ignoring the wrapper [4]
        // $pattern = '@^(?:(.*)(?:/(.*)(?:/(.*))?)?)(?:\.([^/.]+))?($)@U';

        // you just can't beat regex parsing for this
        //          @^                                 ($)@U   match start and end, ungreedy
        //            (?:(.*)          )                       match the controller, ignoring the wrapper              [1]
        //                   (?:/(.*))?                        match the rest of the path if any, ignoring the wrapper [2]
        //                              (?:\.([^/.]+))?        match the extension if any, ignoring the wrapper        [3]
        $pattern = '@^(?:(.*)(?:/(.*))?)(?:\.([^/.]+))?($)@U';
        preg_match($pattern, $path, $matches);

        return array(
            'controller' => $matches[2] === '' ? null : ($matches[1] === '' ? null : $matches[1]),
            'parts'      => $matches[2] === '' ? ($matches[1] === '' ? null : array($matches[1])) : explode('/', $matches[2]),
            'extension'  => $matches[3] === '' ? null : $matches[3],
        );

    }

}


class Deprecated_FrontController
{
    protected $depends;




} // end class FrontController

