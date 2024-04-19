<?php

namespace Sabre\DAVACL;

use Sabre\DAV;
use Sabre\HTTP;

class AllowAccessTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var DAV\Server
     */
    protected $server;

    function setUp(): void {

        $nodes = array(
            new DAV\SimpleCollection('testdir'),
        );

        $this->server = new DAV\Server($nodes);
        $aclPlugin = new Plugin();
        $aclPlugin->allowAccessToNodesWithoutACL = true;
        $this->server->addPlugin($aclPlugin);

    }

    function testGet(): void {

        $this->server->httpRequest->setMethod('GET');
        $this->server->httpRequest->setUrl('/testdir');

        $this->assertTrue($this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]));

    }

    function testGetDoesntExist(): void {

        $this->server->httpRequest->setMethod('GET');
        $this->server->httpRequest->setUrl('/foo');

        $this->assertTrue($this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]));

    }

    function testHEAD(): void {

        $this->server->httpRequest->setMethod('HEAD');
        $this->server->httpRequest->setUrl('/testdir');

        $this->assertTrue($this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]));

    }

    function testOPTIONS(): void {

        $this->server->httpRequest->setMethod('OPTIONS');
        $this->server->httpRequest->setUrl('/testdir');

        $this->assertTrue($this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]));

    }

    function testPUT(): void {

        $this->server->httpRequest->setMethod('PUT');
        $this->server->httpRequest->setUrl('/testdir');

        $this->assertTrue($this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]));

    }

    function testACL(): void {

        $this->server->httpRequest->setMethod('ACL');
        $this->server->httpRequest->setUrl('/testdir');

        $this->assertTrue($this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]));

    }

    function testPROPPATCH(): void {

        $this->server->httpRequest->setMethod('PROPPATCH');
        $this->server->httpRequest->setUrl('/testdir');

        $this->assertTrue($this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]));

    }

    function testCOPY(): void {

        $this->server->httpRequest->setMethod('COPY');
        $this->server->httpRequest->setUrl('/testdir');

        $this->assertTrue($this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]));

    }

    function testMOVE(): void {

        $this->server->httpRequest->setMethod('MOVE');
        $this->server->httpRequest->setUrl('/testdir');

        $this->assertTrue($this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]));

    }

    function testLOCK(): void {

        $this->server->httpRequest->setMethod('LOCK');
        $this->server->httpRequest->setUrl('/testdir');

        $this->assertTrue($this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]));

    }

    function testBeforeBind(): void {

        $this->assertTrue($this->server->emit('beforeBind', ['testdir/file']));

    }


    function testBeforeUnbind(): void {

        $this->assertTrue($this->server->emit('beforeUnbind', ['testdir']));

    }

}
