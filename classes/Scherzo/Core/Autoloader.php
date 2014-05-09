<?php

namespace Scherzo\Core;

class Autoloader
{
    /**
     * An associative array where the key is a (top level) namespace prefix and
     * the value is the base directory for classes in that namespace.
     *
     * @var array
    **/
    protected $prefixes = array();

    /**
     * Register loader with SPL autoloader stack.
    **/
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     * Loads the class file for a given class name.
     *
     * This implementation does not check if the file exists before including
     * it. This is OK as long as there is an error_handler that turns the
     * PHP WARNING into an exception that can be caught (which is normally
     * the case) or error_reporting is set to an appropriate level.
     *
     * @param  string $class The fully-qualified class name.
    **/
    public function loadClass($class)
    {

        try {
            // get the root path for the root namespace
            $path  = $this->namespaces[substr($class, 0, strpos($class, '\\'))];

            // add the path to any sub-namespace
            if ($lastNsPos = strrpos($class, '\\')) {
                $namespace = substr($class, 0, $lastNsPos);
                $class = substr($class, $lastNsPos + 1);
                $path .= "$namespace/";
            }
            // add the class name
            $path = "$path$class.php";
            if ( include $path) {
                return $path;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function setNamespace($namespace, $path)
    {

        $this->namespaces[$namespace] = realpath($path) . DIRECTORY_SEPARATOR;

    }

}
