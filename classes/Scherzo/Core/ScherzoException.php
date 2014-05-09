<?php

namespace Scherzo\Core;

use Exception;

class ScherzoException extends Exception
{
    public $errorLevels = array(
        E_ERROR             => 'Error',
        E_WARNING           => 'Warning',
        E_PARSE             => 'Parse error',
        E_NOTICE            => 'Notice',
        E_CORE_ERROR        => 'Core error',
        E_CORE_WARNING      => 'Core warning',
        E_COMPILE_ERROR     => 'Compile error',
        E_COMPILE_WARNING   => 'Compile warning',
        E_USER_ERROR        => 'User error',
        E_USER_WARNING      => 'User warning',
        E_USER_NOTICE       => 'User notice',
        E_STRICT            => 'Strict error',
        E_RECOVERABLE_ERROR => 'Recoverable error',
        E_DEPRECATED        => 'Deprecated error',
        E_USER_DEPRECATED   => 'User deprecated error',
        E_ALL               => 'E_ALL',
    );

    protected $fromException;
    protected $errorLevel;
    protected $context;

    /**
     *
     *
     * @param  string|array    The message, or an array for translating into a message.  
     * @param  integer|string  A message code.
     * @param  Exception       A previous exception.
     *
    **/
    public function __construct($message = null, $code = 0, $previous = null)
    {
        if (is_array($message)) {
            $msg = array_shift($message);
            try {
                $message = strtr($msg, $message);
            } catch (Exception $ee) {
                $message = $ee->getMessage() . " after $msg";
            }
        }
        parent::__construct($message, $code, $previous);
    }

    public function getStackTrace()
    {
        if ($this->ee === null) {
            return $this->getTrace();
        } else {
            return $this->ee->getTrace();
        }
    }

    /**
     * Create a ScherzoException from error handler arguments.
     *
     * @param   array  The arguments passed to the registered error handler.
     * @return  $this  (chainable)
    **/
    public function fromError($args)
    {
        $this->errorLevel = $args[0];
        $this->message = $args[1];
        $this->file = $args[2];
        $this->line = $args[3];
        $this->context = $args[4];
        return $this; // chainable
    }

    /**
     * Create a ScherzoException from a plain Exception.
     *
     * @param   Exception  A plain Exception
     * @return  $this  (chainable)
    **/
    public function fromException($e) {
        $this->ee = $e;
        $this->code = $e->getCode();
        $this->file = $e->getFile();
        $this->line = $e->getLine();
        $this->message = $e->getMessage();
        return $this; // chainable
    }


    /**
     * Convert error_get_last() to an exception.
     *
     * @param   array  Result from error_get_last().
     * @return  $this  (chainable)
    **/
    public function fromLastError($error)
    {
        $this->errorLevel = $error['type'];
        $this->message = $error['message'];
        $this->file = $error['file'];
        $this->line = $error['line'];
        return $this; // chainable
    }
} // end class Exception
