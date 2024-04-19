<?php

namespace Sabre\HTTP;

class RequestTest extends \PHPUnit_Framework_TestCase {

    function testConstruct(): void {

        $request = new Request('GET', '/foo', [
            'User-Agent' => 'Evert',
        ]);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/foo', $request->getUrl());
        $this->assertEquals([
            'User-Agent' => ['Evert'],
        ], $request->getHeaders());

    }

    function testGetQueryParameters(): void {

        $request = new Request('GET', '/foo?a=b&c&d=e');
        $this->assertEquals([
            'a' => 'b',
            'c' => null,
            'd' => 'e',
        ], $request->getQueryParameters());

    }

    function testGetQueryParametersNoData(): void {

        $request = new Request('GET', '/foo');
        $this->assertEquals([], $request->getQueryParameters());

    }

    /**
     * @backupGlobals
     */
    function testCreateFromPHPRequest(): void {

        $_SERVER['REQUEST_METHOD'] = 'PUT';

        $request = Sapi::getRequest();
        $this->assertEquals('PUT', $request->getMethod());

    }

    function testGetAbsoluteUrl(): void {

        $s = [
            'HTTP_HOST'   => 'sabredav.org',
            'REQUEST_URI' => '/foo'
        ];

        $r = Sapi::createFromServerArray($s);

        $this->assertEquals('http://sabredav.org/foo', $r->getAbsoluteUrl());

        $s = [
            'HTTP_HOST'   => 'sabredav.org',
            'REQUEST_URI' => '/foo',
            'HTTPS'       => 'on',
        ];

        $r = Sapi::createFromServerArray($s);

        $this->assertEquals('https://sabredav.org/foo', $r->getAbsoluteUrl());

    }

    function testGetPostData(): void {

        $post = [
            'bla' => 'foo',
        ];
        $r = new Request();
        $r->setPostData($post);
        $this->assertEquals($post, $r->getPostData());

    }

    function testGetPath(): void {

        $request = new Request();
        $request->setBaseUrl('/foo');
        $request->setUrl('/foo/bar/');

        $this->assertEquals('bar', $request->getPath());

    }

    function testGetPathStrippedQuery(): void {

        $request = new Request();
        $request->setBaseUrl('/foo');
        $request->setUrl('/foo/bar/?a=b');

        $this->assertEquals('bar', $request->getPath());

    }

    function testGetPathMissingSlash(): void {

        $request = new Request();
        $request->setBaseUrl('/foo/');
        $request->setUrl('/foo');

        $this->assertEquals('', $request->getPath());

    }

    /**
     * @expectedException \LogicException
     */
    function testGetPathOutsideBaseUrl(): void {

        $request = new Request();
        $request->setBaseUrl('/foo/');
        $request->setUrl('/bar/');

        $request->getPath();

    }

    function testToString(): void {

        $request = new Request('PUT', '/foo/bar', ['Content-Type' => 'text/xml']);
        $request->setBody('foo');

        $expected = <<<HI
PUT /foo/bar HTTP/1.1\r
Content-Type: text/xml\r
\r
foo
HI;
        $this->assertEquals($expected, (string)$request);

    }

    function testToStringAuthorization(): void {

        $request = new Request('PUT', '/foo/bar', ['Content-Type' => 'text/xml', 'Authorization' => 'Basic foobar']);
        $request->setBody('foo');

        $expected = <<<HI
PUT /foo/bar HTTP/1.1\r
Content-Type: text/xml\r
Authorization: Basic REDACTED\r
\r
foo
HI;
        $this->assertEquals($expected, (string)$request);

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testConstructorWithArray(): void {

        $request = new Request([]);

    }

}
