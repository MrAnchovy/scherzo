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
 * Local settings base class.
**/
class Local
{
    /** Initial options set in `index.php`. */
    protected $coreInitialOptions;

    /** Set this to an object in the constructor to override the Scherzo autoloader. */
    public $coreAutoloaderObject;

    /** Set this to an object in the constructor to override the Scherzo container. */
    public $coreContainerObject;

    /** The controller used to display 404 etc. errors. */
    public $coreErrorController = '\Scherzo\Core\ErrorController';

    /** The timezone to use if it is not forced or set in php.ini. */
    public $coreFallbackTimezone = 'UTC';

    /** Default services - do not modify this, use `$coreServices` instead. */
    private $coreDefaultServices = array(
        'frontController' => 'Scherzo\Core\FrontController',
        'phpFlow' => 'Scherzo\Core\PhpFlow',
        'httpRequest' => 'Scherzo\Core\HttpRequest',
        'cliRequest' => 'Scherzo\Core\CliRequest',
        'testRequest' => 'Scherzo\Core\TestRequest',
    );

    /** Additional services. */
    public $coreServices = array();

    /** Force the default timezone. */
    public $coreTimezone;

    public function __construct($initialOptions)
    {
        $this->coreInitialOptions = $initialOptions;
        $this->coreBaseUrl    = isset($initialOptions->baseUrl)    ? $initialOptions->baseUrl    : '';
        $this->coreStartTime  = isset($initialOptions->startTime)  ? $initialOptions->startTime  : microtime(true);
        $this->coreDeployment = isset($initialOptions->deployment) ? $initialOptions->deployment : null;
        $this->coreDirectory  = realpath(
            isset($initialOptions->scherzoDirectory)
                ? $initialOptions->scherzoDirectory
                : __DIR__.'/../..') . DIRECTORY_SEPARATOR;
        $this->coreServices   = array_merge($this->coreDefaultServices, $this->coreServices);
    }

    public function getInitialOption($name, $default = null)
    {
        return isset($this->initialOptions->$name) ? $this->initialOptions->$name : $default;
    }

    public function isProduction()
    {
        return $this->coreDeployment === null;
    }

}
