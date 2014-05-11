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
 * Handle exceptions, errors and shutdown.
 *
 * @todo import legacy code.
 *
 *
 * Normal execution
 *     - code calls shutdown()
 *     - set status to CONTROLLED_START
 *     - clean up and call hooks
 *     - set status to CONTROLLED_COMPLETE
 *     - call exit()
 *     - PHP calls shutdownHandler()
 *     - shutdownHandler checks CONTROLLED_COMPLETE
 *     - shutdownHandler calls exit()
 *
 * Fatal error trapping
 *     - PHP calls shutdownHandler()
 *     - set status to FATAL_START
 *     - shutdownHandler creates an exception and invokes the error controller
 *     - shutdownHandler calls shutdown()
 *     - clean up and call hooks
 *     - set status to FATAL_COMPLETE
 *     - returns to shutdownHandler
 *     - call exit()
 *
 * @package  Scherzo\Core
**/
class PhpFlow extends \Scherzo\Core\Service
{

    const CONTROLLED_START = 1;
    const CONTROLLED_INCOMPLETE = 2;
    const CONTROLLED_COMPLETE = 3;
    const FATAL_START = 4;
    const FATAL_COMPLETE = 5;

    /**
     * Flag to indicate a controlled shutdown.
     *
     * @var null|true
    **/
    protected $progress;

    /**
     * @param  integer  $level
     * @param  string   $message
     * @param  string   $file
     * @param  integer  $line
     * @param  array    $context  All variables in scope.
    **/
    public function errorHandler($level, $message, $file, $line, $context)
    {
        // rethrow as ScherzoException
        $e = new ScherzoException();
        $e->fromError(func_get_args());
        throw $e;
    }

    /**
     * Exception handler.
    **/
    public function exceptionHandler(Exception $e)
    {
        try {
            // convert to a ScherzoException if necessary
            if (!is_a($e, '\Scherzo\Core\ScherzoException')) {
                $ee = new ScherzoException();
                $e = $ee->fromException($e);
            }
            $this->displayException($e);
            $this->shutdown();

            throw new ScherzoException('Should never get here');

            try {
                $this->depends->log
                    ->logError("Error {$e->status} " . $e->getMessage());
            } catch (Exception $e) {
            }
            $controller = new ErrorController($this->depends);
            $controller->setException($e);
            $controller->execute();
        } catch (Exception $eee) {
            if (!headers_sent()) {
                header('HTTP/1.1 500 Internal Server Error');
                header('Content-Type: text/plain');
            }
            echo "Error in exception handler: " . (string)$eee;
        }
    }

    /**
     * Set up error and exception handling.
    **/
    protected function afterConstructor() {
        ini_set('display_errors', 0);
        error_reporting(-1);
        // @todo REVISIT there is probably a best order to do these in
        set_exception_handler(array($this, 'exceptionHandler'));
        set_error_handler(array($this, 'errorHandler'), -1);      // handle all errors
        register_shutdown_function(array($this, 'shutdownHandler'));
    }

    /**
     * Perform a controlled shutdown.
     *
     * @todo implement hooks for e.g. logging
     *
     * @package  Scherzo\Core
    **/
    public function shutdown()
    {
        flush();
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        switch ($this->progress) {
            case null :
                // normal shutdown
                try {
                    $this->progress = self::CONTROLLED_START;
                    // $this->shutdownError('normal shutdown (debug message)');
                    // @todo call service hooks
                    $this->progress = self::CONTROLLED_COMPLETE;
                    // PHP will invoke shutdownHandler
                    exit(0);
                } catch (Exception $e) {
                    $this->progress = self::CONTROLLED_INCOMPLETE;
                    $this->shutdownError('exception during normal shutdown');
                    // PHP will invoke shutdownHandler
                    exit(1);
                }
            case self::FATAL_START :
                // shutdown after fatal error
                try {
                    // $this->shutdownError('normal shutdown after fatal error (debug message)');
                    // @todo call service hooks
                    $this->progress = self::FATAL_COMPLETE;
                    // return to shutdownHandler
                    return;
                } catch (Exception $e) {
                    $this->shutdownError('exception during shutdown after fatal error');
                    // return to shutdownHandler
                    return;
                }
            default :
                // this should never happen
                $this->shutdownError("unknown progress value [$this->progress] in shutdown");
                exit(1);
        }
    }

    /**
     * Deal with a shutdown error.
    **/
    public function shutdownError($message)
    {
        if (!$this->depends->local->isProduction()) {
            echo "Shutdown error - $message.<br>\n";
        }
    }

    /**
     * Shutdown handler.
    **/
    public function secondShutdownHandler()
    {
        if ($this->progress !== self::FATAL_COMPLETE) {
            $this->shutdownError('fatal error during fatal error handling');
        }
    }

    /**
     * Shutdown handler.
    **/
    public function shutdownHandler()
    {
        switch ($this->progress) {
            case self::CONTROLLED_COMPLETE :
            case self::FATAL_COMPLETE :
                // $this->shutdownError('shutdown handler normal shutdown (debug message)');
                // normal shutdown
                exit(0);
            case null :
                // fatal error
                try {
                    // $this->shutdownError('shutdown handler fatal error (debug message)');
                    $this->progress = self::FATAL_START;
                    if ($error = error_get_last()) {
                        $e = new ScherzoException;
                        $e->fromLastError($error);
                    } else {
                        $e = new ScherzoException('Fatal error trapped but not reported by error_get_last');
                    }
                    $this->displayException($e);
                    $this->shutdown();
                    // $this->shutdownError('shutdown handler fatal error complete (debug message)');
                    exit(1);
                } catch (Exception $e) {
                    $this->shutdownError('exception during fatal error handling');
                    exit(1);
                }
            case self::CONTROLLED_INCOMPLETE :
                // $this->shutdownError('shutdown handler error during normal shutdown (debug message)');
                // error during normal shutdown, already logged
                exit(1);
            case self::CONTROLLED_START :
                $this->shutdownError('fatal error during normal shutdown');
                exit(1);
            case self::FATAL_START :
                $this->shutdownError('fatal error during fatal error handling');
                exit(1);
            default :
                $this->shutdownError("unknown progress status [$this->progress] in shutdown handler");
                exit(1);
        }
    }

    public function displayException($e)
    {
        try {
            if (include $this->depends->local->coreDirectory.'templates/Scherzo/error/debug.php') {
            } else {
                echo 'Could not display debug dump';
            }
        } catch (Exception $ee) {
            echo 'Could not display debug dump';
        }
    }
}
