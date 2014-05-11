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
 * Error controller.
**/
class ErrorController
{

    public function execute_404($route)
    {
        echo '404 page';
        print_r($route);
    }
}
