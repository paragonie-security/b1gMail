<?php

namespace Sabre\CardDAV;

use Sabre\DAV;
use Sabre\DAVACL;
use Sabre\HTTP;

abstract class AbstractPluginTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Sabre\CardDAV\Plugin
     */
    protected $plugin;
    /**
     * @var Sabre\DAV\Server
     */
    protected $server;
    /**
     * @var Sabre\CardDAV\Backend\Mock;
     */
    protected $backend;



}
