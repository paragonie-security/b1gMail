<?php

namespace Sabre\DAV\Auth;

use Sabre\HTTP;
use Sabre\DAV;

require_once 'Sabre/HTTP/ResponseMock.php';

class PluginTest extends \PHPUnit_Framework_TestCase {

    function testInit(): void {

        $fakeServer = new DAV\Server( new DAV\SimpleCollection('bla'));
        $plugin = new Plugin(new Backend\Mock(),'realm');
        $this->assertTrue($plugin instanceof Plugin);
        $fakeServer->addPlugin($plugin);
        $this->assertEquals($plugin, $fakeServer->getPlugin('auth'));
        $this->assertInternalType('array', $plugin->getPluginInfo());

    }

    /**
     * @depends testInit
     */
    function testAuthenticate(): void {

        $fakeServer = new DAV\Server( new DAV\SimpleCollection('bla'));
        $plugin = new Plugin(new Backend\Mock());
        $fakeServer->addPlugin($plugin);
        $this->assertTrue(
            $fakeServer->emit('beforeMethod', [new HTTP\Request(), new HTTP\Response()])
        );

    }

    /**
     * @depends testInit
     * @expectedException Sabre\DAV\Exception\NotAuthenticated
     */
    function testAuthenticateFail(): void {

        $fakeServer = new DAV\Server( new DAV\SimpleCollection('bla'));
        $backend = new Backend\Mock();
        $backend->fail = true;

        $plugin = new Plugin($backend);
        $fakeServer->addPlugin($plugin);
        $fakeServer->emit('beforeMethod', [new HTTP\Request(), new HTTP\Response()]);

    }

    /**
     * @depends testAuthenticate
     */
    function testMultipleBackend(): void {

        $fakeServer = new DAV\Server( new DAV\SimpleCollection('bla'));
        $backend1 = new Backend\Mock();
        $backend2 = new Backend\Mock();
        $backend2->fail = true;

        $plugin = new Plugin();
        $plugin->addBackend($backend1);
        $plugin->addBackend($backend2);

        $fakeServer->addPlugin($plugin);
        $fakeServer->emit('beforeMethod', [new HTTP\Request(), new HTTP\Response()]);

        $this->assertEquals('principals/admin', $plugin->getCurrentPrincipal());

    }

    /**
     * @depends testInit
     * @expectedException Sabre\DAV\Exception
     */
    function testNoAuthBackend(): void {

        $fakeServer = new DAV\Server( new DAV\SimpleCollection('bla'));

        $plugin = new Plugin();
        $fakeServer->addPlugin($plugin);
        $fakeServer->emit('beforeMethod', [new HTTP\Request(), new HTTP\Response()]);

    }
    /**
     * @depends testInit
     * @expectedException Sabre\DAV\Exception
     */
    function testInvalidCheckResponse(): void {

        $fakeServer = new DAV\Server( new DAV\SimpleCollection('bla'));
        $backend = new Backend\Mock();
        $backend->invalidCheckResponse = true;

        $plugin = new Plugin($backend);
        $fakeServer->addPlugin($plugin);
        $fakeServer->emit('beforeMethod', [new HTTP\Request(), new HTTP\Response()]);

    }

    /**
     * @depends testAuthenticate
     */
    function testGetCurrentPrincipal(): void {

        $fakeServer = new DAV\Server( new DAV\SimpleCollection('bla'));
        $plugin = new Plugin(new Backend\Mock());
        $fakeServer->addPlugin($plugin);
        $fakeServer->emit('beforeMethod', [new HTTP\Request(), new HTTP\Response()]);
        $this->assertEquals('principals/admin', $plugin->getCurrentPrincipal());

    }

    /**
     * @depends testAuthenticate
     */
    function testGetCurrentUser(): void {

        $fakeServer = new DAV\Server( new DAV\SimpleCollection('bla'));
        $plugin = new Plugin(new Backend\Mock());
        $fakeServer->addPlugin($plugin);
        $fakeServer->emit('beforeMethod', [new HTTP\Request(), new HTTP\Response()]);
        $this->assertEquals('admin', $plugin->getCurrentUser());

    }

}

