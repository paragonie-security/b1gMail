<?php

namespace Sabre\DAV;
use Sabre\HTTP;

require_once 'Sabre/DAV/AbstractServer.php';
require_once 'Sabre/DAV/TestPlugin.php';

class ServerPluginTest extends AbstractServer {

    /**
     * @var Sabre\DAV\TestPlugin
     */
    protected $testPlugin;

    function setUp(): void {

        parent::setUp();

        $testPlugin = new TestPlugin();
        $this->server->addPlugin($testPlugin);
        $this->testPlugin = $testPlugin;

    }

    /**
     */
    function testBaseClass(): void {

        $p = new ServerPluginMock();
        $this->assertEquals([],$p->getFeatures());
        $this->assertEquals([],$p->getHTTPMethods(''));
        $this->assertEquals(
            [
                'name' => 'Sabre\DAV\ServerPluginMock',
                'description' => null,
                'link' => null
            ], $p->getPluginInfo()
        );

    }

    function testOptions(): void {

        $serverVars = array(
            'REQUEST_URI'    => '/',
            'REQUEST_METHOD' => 'OPTIONS',
        );

        $request = HTTP\Sapi::createFromServerArray($serverVars);
        $this->server->httpRequest = ($request);
        $this->server->exec();

        $this->assertEquals(array(
            'DAV'             => ['1, 3, extended-mkcol, drinking'],
            'MS-Author-Via'   => ['DAV'],
            'Allow'           => ['OPTIONS, GET, HEAD, DELETE, PROPFIND, PUT, PROPPATCH, COPY, MOVE, REPORT, BEER, WINE'],
            'Accept-Ranges'   => ['bytes'],
            'Content-Length'  => ['0'],
            'X-Sabre-Version' => [Version::VERSION],
        ),$this->response->getHeaders());

        $this->assertEquals(200, $this->response->status);
        $this->assertEquals('', $this->response->body);
        $this->assertEquals('OPTIONS',$this->testPlugin->beforeMethod);


    }

    function testGetPlugin(): void {

        $this->assertEquals($this->testPlugin,$this->server->getPlugin(get_class($this->testPlugin)));

    }

    function testUnknownPlugin(): void {

        $this->assertNull($this->server->getPlugin('SomeRandomClassName'));

    }

    function testGetSupportedReportSet(): void {

        $this->assertEquals(array(), $this->testPlugin->getSupportedReportSet('/'));

    }

    function testGetPlugins(): void {

        $this->assertEquals(
            array(
                get_class($this->testPlugin) => $this->testPlugin,
                'core' => $this->server->getPlugin('core'),
            ),
            $this->server->getPlugins()
        );

    }


}

class ServerPluginMock extends ServerPlugin {

    function initialize(Server $s) { }

}
