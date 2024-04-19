<?php

namespace Sabre\DAVACL;

use Sabre\DAV;
use Sabre\HTTP;

class BlockAccessTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var DAV\Server
     */
    protected $server;
    protected $plugin;

    function setUp(): void {

        $nodes = [
            new DAV\SimpleCollection('testdir'),
        ];

        $this->server = new DAV\Server($nodes);
        $this->plugin = new Plugin();
        $this->plugin->allowAccessToNodesWithoutACL = false;
        $this->server->addPlugin($this->plugin);

    }

    /**
     * @expectedException Sabre\DAVACL\Exception\NeedPrivileges
     */
    function testGet(): void {

        $this->server->httpRequest->setMethod('GET');
        $this->server->httpRequest->setUrl('/testdir');

        $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);

    }

    function testGetDoesntExist(): void {

        $this->server->httpRequest->setMethod('GET');
        $this->server->httpRequest->setUrl('/foo');

        $r = $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);
        $this->assertTrue($r);

    }

    /**
     * @expectedException Sabre\DAVACL\Exception\NeedPrivileges
     */
    function testHEAD(): void {

        $this->server->httpRequest->setMethod('HEAD');
        $this->server->httpRequest->setUrl('/testdir');

        $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);

    }

    /**
     * @expectedException Sabre\DAVACL\Exception\NeedPrivileges
     */
    function testOPTIONS(): void {

        $this->server->httpRequest->setMethod('OPTIONS');
        $this->server->httpRequest->setUrl('/testdir');

        $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);

    }

    /**
     * @expectedException Sabre\DAVACL\Exception\NeedPrivileges
     */
    function testPUT(): void {

        $this->server->httpRequest->setMethod('PUT');
        $this->server->httpRequest->setUrl('/testdir');

        $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);

    }

    /**
     * @expectedException Sabre\DAVACL\Exception\NeedPrivileges
     */
    function testPROPPATCH(): void {

        $this->server->httpRequest->setMethod('PROPPATCH');
        $this->server->httpRequest->setUrl('/testdir');

        $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);

    }

    /**
     * @expectedException Sabre\DAVACL\Exception\NeedPrivileges
     */
    function testCOPY(): void {

        $this->server->httpRequest->setMethod('COPY');
        $this->server->httpRequest->setUrl('/testdir');

        $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);

    }

    /**
     * @expectedException Sabre\DAVACL\Exception\NeedPrivileges
     */
    function testMOVE(): void {

        $this->server->httpRequest->setMethod('MOVE');
        $this->server->httpRequest->setUrl('/testdir');

        $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);

    }

    /**
     * @expectedException Sabre\DAVACL\Exception\NeedPrivileges
     */
    function testACL(): void {

        $this->server->httpRequest->setMethod('ACL');
        $this->server->httpRequest->setUrl('/testdir');

        $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);

    }

    /**
     * @expectedException Sabre\DAVACL\Exception\NeedPrivileges
     */
    function testLOCK(): void {

        $this->server->httpRequest->setMethod('LOCK');
        $this->server->httpRequest->setUrl('/testdir');

        $this->server->emit('beforeMethod', [$this->server->httpRequest, $this->server->httpResponse]);

    }

    /**
     * @expectedException Sabre\DAVACL\Exception\NeedPrivileges
     */
    function testBeforeBind(): void {

        $this->server->emit('beforeBind', ['testdir/file']);

    }

    /**
     * @expectedException Sabre\DAVACL\Exception\NeedPrivileges
     */
    function testBeforeUnbind(): void {

        $this->server->emit('beforeUnbind', ['testdir']);

    }

    function testPropFind(): void {

        $propFind = new DAV\PropFind('testdir', [
            '{DAV:}displayname',
            '{DAV:}getcontentlength',
            '{DAV:}bar',
            '{DAV:}owner',
        ]);

        $r = $this->server->emit('propFind', [$propFind, new DAV\SimpleCollection('testdir')]);
        $this->assertTrue($r);

        $expected = [
            200 => [],
            404 => [],
            403 => [
                '{DAV:}displayname' => null,
                '{DAV:}getcontentlength' => null,
                '{DAV:}bar' => null,
                '{DAV:}owner' => null,
            ],
        ];

        $this->assertEquals($expected, $propFind->getResultForMultiStatus());

    }

    function testBeforeGetPropertiesNoListing(): void {

        $this->plugin->hideNodesFromListings = true;
        $propFind = new DAV\PropFind('testdir', [
            '{DAV:}displayname',
            '{DAV:}getcontentlength',
            '{DAV:}bar',
            '{DAV:}owner',
        ]);

        $r = $this->server->emit('propFind', [$propFind, new DAV\SimpleCollection('testdir')]);
        $this->assertFalse($r);

    }
}
