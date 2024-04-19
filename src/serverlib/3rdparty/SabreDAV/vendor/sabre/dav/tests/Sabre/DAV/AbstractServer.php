<?php

namespace Sabre\DAV;

use Sabre\HTTP;

abstract class AbstractServer extends \PHPUnit_Framework_TestCase {

    /**
     * @var Sabre\HTTP\ResponseMock
     */
    protected $response;
    protected $request;
    /**
     * @var Sabre\DAV\Server
     */
    protected $server;
    protected $tempDir = SABRE_TEMPDIR;









}
