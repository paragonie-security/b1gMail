<?php

// SabreDAV test server.

class CliLog {

    protected $stream;

    function __construct() {

        $this->stream = fopen('php://stdout','w');

    }



}

new CliLog();

if (php_sapi_name()!=='cli-server') {
    die("This script is intended to run on the built-in php webserver");
}

// Finding composer


$paths = array(
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',
);

foreach($paths as $path) {
    if (file_exists($path)) {
        include $path;
        break;
    }
}

use Sabre\DAV;

// Root 
$root = new DAV\FS\Directory(getcwd());

// Setting up server.
$server = new DAV\Server($root);

// Browser plugin
$server->addPlugin(new DAV\Browser\Plugin());

$server->exec();
