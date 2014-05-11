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
 * PSR-4 class autoloader.
**/
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
     * it for efficiency, which is fine for the autoloading strategy used (each
     * namespace has only one base directory).
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

            if (include $path) {
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
