<?php
/**
 * index.php
 *
 * This file is part of the Scherzo PHP application framework.
 *
 * This is the single entry point for a Scherzo application.
 *
 * @link       http://github.com/MrAnchovy/scherzo
 * @copyright  Copyright © 2014 [MrAnchovy](http://www.mranchovy.com/)
 * @license    MIT
**/

error_reporting(0);
$options = new StdClass;
$options->startTime = microtime(true);

// no need to modify anything before here =====================================

// Leave this outfor production or 'dev'/'test'/'stage'
// $options->deployment = 'dev';
$options->deployment = 'coreDev';

// path to your application's local settings
$options->localFile = __DIR__ . '/local/scherzo-demo.local.php';

// for a 'quick start' installation (single directory under the web root)
$options->scherzoDirectory = __DIR__ . DIRECTORY_SEPARATOR;

// for 'production' installation accessible as www.example.com/myapp/
// $options->scherzoDirectory = __DIR__ . '/../../vendor/scherzo/scherzo/';
// $options->localFile = __DIR__ . '/../../local/scherzo-demo.local.php';

// no need to modify anything after here ======================================


// attempt to load and execute bootstrap
if (include $options->scherzoDirectory . '/classes/Scherzo/Core/Bootstrap.php') {

    Scherzo\Core\Bootstrap::Bootstrap($options);

} else {

    // fallback if bootstrap not found
    if (!headers_sent()) {
        header_remove();
        header('HTTP/1.0 503 Service Unavailable');
        header('Content-Type: text/plain');
    }
    if ($options->deployment === null) {
        echo 'This site is closed for maintenance, please come back later.';
    } else {
        echo 'Could not find bootstrap.';
    }
    exit(1);
}
