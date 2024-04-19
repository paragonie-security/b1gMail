<?php

namespace Sabre\DAV;

use Sabre\HTTP;

class HTTPPreferParsingTest extends \Sabre\DAVServerTest {

    function testParseSimple(): void {

        $httpRequest = HTTP\Sapi::createFromServerArray([
            'HTTP_PREFER' => 'return-asynch',
        ]);

        $server = new Server();
        $server->httpRequest = $httpRequest;

        $this->assertEquals([
            'respond-async' => true,
            'return'        => null,
            'handling'      => null,
            'wait'          => null,
        ], $server->getHTTPPrefer());

    }

    function testParseValue(): void {

        $httpRequest = HTTP\Sapi::createFromServerArray([
            'HTTP_PREFER' => 'wait=10',
        ]);

        $server = new Server();
        $server->httpRequest = $httpRequest;

        $this->assertEquals([
            'respond-async' => false,
            'return'        => null,
            'handling'      => null,
            'wait'          => '10',
        ], $server->getHTTPPrefer());

    }

    function testParseMultiple(): void {

        $httpRequest = HTTP\Sapi::createFromServerArray([
            'HTTP_PREFER' => 'return-minimal, strict,lenient',
        ]);

        $server = new Server();
        $server->httpRequest = $httpRequest;

        $this->assertEquals([
            'respond-async' => false,
            'return'        => 'minimal',
            'handling'      => 'lenient',
            'wait'          => null,
        ], $server->getHTTPPrefer());

    }

    function testParseWeirdValue(): void {

        $httpRequest = HTTP\Sapi::createFromServerArray([
            'HTTP_PREFER' => 'BOOOH',
        ]);

        $server = new Server();
        $server->httpRequest = $httpRequest;

        $this->assertEquals([
            'respond-async' => false,
            'return'        => null,
            'handling'      => null,
            'wait'          => null,
            'boooh'         => true,
        ], $server->getHTTPPrefer());

    }

    function testBrief(): void {

        $httpRequest = HTTP\Sapi::createFromServerArray([
            'HTTP_BRIEF' => 't',
        ]);

        $server = new Server();
        $server->httpRequest = $httpRequest;

        $this->assertEquals([
            'respond-async' => false,
            'return'        => 'minimal',
            'handling'      => null,
            'wait'          => null,
        ], $server->getHTTPPrefer());

    }

    /**
     * propfindMinimal
     *
     * @return void
     */
    function testpropfindMinimal(): void {

        $request = HTTP\Sapi::createFromServerArray([
            'REQUEST_METHOD' => 'PROPFIND',
            'REQUEST_URI'    => '/',
            'HTTP_PREFER'    => 'return-minimal',
        ]);
        $request->setBody(<<<BLA
<?xml version="1.0"?>
<d:propfind xmlns:d="DAV:">
    <d:prop>
        <d:something />
        <d:resourcetype />
    </d:prop>
</d:propfind>
BLA
        );

        $response = $this->request($request);

        $body = $response->getBodyAsString();

        $this->assertEquals(207, $response->getStatus(), $body);

        $this->assertTrue(strpos($body, 'resourcetype') !== false, $body);
        $this->assertTrue(strpos($body, 'something') === false, $body);

    }

    function testproppatchMinimal(): void {

        $request = new HTTP\Request('PROPPATCH', '/', ['Prefer' => 'return-minimal']);
        $request->setBody(<<<BLA
<?xml version="1.0"?>
<d:propertyupdate xmlns:d="DAV:">
    <d:set>
        <d:prop>
            <d:something>nope!</d:something>
        </d:prop>
    </d:set>
</d:propertyupdate>
BLA
        );

        $this->server->on('propPatch', function($path, PropPatch $propPatch) {

            $propPatch->handle('{DAV:}something', function($props) {
                return true;
            });

        });

        $response = $this->request($request);

        $this->assertEquals(0, strlen($response->body), 'Expected empty body: ' . $response->body);
        $this->assertEquals(204, $response->status);

    }

    function testproppatchMinimalError(): void {

        $request = new HTTP\Request('PROPPATCH', '/', ['Prefer' => 'return-minimal']);
        $request->setBody(<<<BLA
<?xml version="1.0"?>
<d:propertyupdate xmlns:d="DAV:">
    <d:set>
        <d:prop>
            <d:something>nope!</d:something>
        </d:prop>
    </d:set>
</d:propertyupdate>
BLA
        );

        $response = $this->request($request);

        $body = $response->getBodyAsString();

        $this->assertEquals(207, $response->status);
        $this->assertTrue(strpos($body, 'something') !== false);
        $this->assertTrue(strpos($body, '403 Forbidden') !== false, $body);

    }
}
