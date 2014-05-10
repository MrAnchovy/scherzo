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
 * Bootstrapping for the Scherzo framework.
 *
 * package  Scherzo\Core
**/
class Bootstrap
{

    public static function timer($label, $set = null) {
        static $start;
        static $last;
        if ($set !== null) {
            $start = $last = $set;
        }
        $lap = microtime(true);
        echo "$label " . number_format(($lap - $last) * 1e6, 0) . " us<br>\n";
        $last = $lap;
    }

    /**
     * Bootstrap everything.
     *
     * This establishes the local settings, the class autoloader, flow control
     * (error, exception and shutdown handling) and the front controller.
     *
     * @param  StdClass  $options  Options set in index.php.
    **/
    public static function bootstrap($options) {

        try {
            // static::timer('To start bootstrap', $options->startTime);

            // make sure errors are dealt with appropriately for the deployment
            if (isset($options->deployment)) {
                // show bootstrapping errors but hide include failures
                error_reporting(~E_WARNING);
                ini_set('display_errors', 1);
            } else {
                // production deployment - hide any bootstrapping errors
                error_reporting(0);
            }

            // get the local settings - these can override EVERYTHING, even the scherzo container
            $local = static::getLocal($options);
            // static::timer('To get local');

            // make sure a timezone is set using optional local setting
            static::bootstrapTimezone($local);
            // static::timer('To set timezone');

            // bootstrap the autoloader
            $autoloader = static::getAutoloader($local);
            // static::timer('To get autoloader');

            // bootstrap the container for dependency injection
            $container = static::getContainer($local);
            // static::timer('To get container');

            // load the services just created
            $container->local = $local;
            $container->autoloader = $autoloader;

            // register the other services defined in Local so they can be lazy-loaded
            $container->register($local->coreServices);

            // lazy-load error, exception and shutdown handling
            $container->phpFlow;
            // static::timer('To load phpFlow');

        } catch (Exception $e) {

            static::bootstrapError($options, $e);
            // may return here in unit test mode
            return;
        }

        // lazy-load and execute the Front Controller
        $container->frontController->execute();
        // static::timer('To load and execute front controller');

        // controlled shutdown
        $container->phpFlow->shutdown();

        // should never get here
        throw new Exception('phpFlow did not exit');

    }

    /**
     * Deal with an error in bootstrap.
     *
     * See installation instructions in README.
     *
     * @param  Scherzo\Core\Local  $local  Local settings object.
    **/
    protected static function bootstrapError($options, $e)
    {
        $message = '';

        if (isset($options->deployment)) {
            $message .= 'Scherzo bootstrap error - ' . $e->getMessage()
                . ' in line ' . $e->getLine()
                . ' of ' . $e->getFile() . ".\n\n"
                . "If deployed in production only the following message is displayed:\n\n";
        }

        $message .= 'This site is temporarily closed for maintenance, please come back later.';

        if (isset($options->unittest)) {
            // no headers or exit when run from phpunit
            echo $message;
            return;
        } else {
            if (!headers_sent()) {
                header_remove();
                header('HTTP/1.0 503 Service Unavailable');
                header('Content-Type: text/plain');
            }
            echo $message;
            exit(1);
        }
    }

    /**
     * Deal with unset default timezone.
     *
     * @todo   Test behaviour with PHP 5.3.
     *
     * @param  Scherzo\Core\Local  $local  Local settings object.
    **/
    protected static function bootstrapTimezone($local)
    {
        if ($local->coreTimezone) {
            // if it is set explicitly, use it
            date_default_set($local->coreTimezone);
        } else {
            // this is the only way to check it in PHP >= 5.4.0
            if (!ini_get('date.timezone')) {
                date_default_timezone_set($local->coreFallbackTimezone);
            }
        }
    }

    /**
     * Get the autoloader and initialise and register it.
     *
     * @param   Scherzo\Core\Local       $local  Local settings object.
     * @return  Scherzo\Core\Autoloader  Autoload handler.
    **/
    protected static function getAutoloader($local)
    {
        // autoloader
        if (isset($local->coreAutoloaderObject)) {
            $autoloader = $local->coreAutoloaderObject;
        } else {
            require_once __DIR__.'/Autoloader.php';
            $autoloader = new Autoloader;
        }
        $autoloader->setNamespace('Scherzo', $local->coreDirectory . 'classes');
        $autoloader->register();
        return $autoloader;
    }

    /**
     * Get the dependency injection container.
     *
     * @param   Scherzo\Core\Local  $local  Local settings object.
     * @return  Scherzo\Scherzo     Dependency injection container.
    **/
    protected static function getContainer($local)
    {
        // use a custom container if one has been provided
        if (isset($local->coreContainerObject)) {
            return $local->coreContainerObject;
        } else {
            // load the default container
            return new \Scherzo\Scherzo;
        }
    }

    /**
     * Get the local configuration object.
     *
     * There is no error handling yet so this code needs to fail safe.
     *
     * @param   StdClass            $initialOptions  Options set in index.php.
     * @return  Scherzo\Core\Local  Local settings object.
    **/
    protected static function getLocal($initialOptions) {

        // load the base class, unless a customised bootstrap has done it already
        if (!class_exists('\Scherzo\Core\Local')) {
            include __DIR__ . '/Local.php';
        }

        $localFile = $initialOptions->localFile;

        // load the local file (which defines Local extending \Scherzo\Local)
        if (!include $localFile) {
            throw new Exception("local file $localFile does not exist");
        };

        if (isset($initialOptions->deployment)) {
            // not production so insert nonprod before .php
            $nonProdFile = substr($localFile, 0, strlen($localFile) - 3) . 'nonprod.php';
            if (include $nonProdFile) {
                return new \LocalNonProduction($initialOptions);
            } else {
                return new \Local($initialOptions);
            }
        } else {
            // production mode so use the base Local class
            return new \Local($initialOptions);
        }
    }
}
