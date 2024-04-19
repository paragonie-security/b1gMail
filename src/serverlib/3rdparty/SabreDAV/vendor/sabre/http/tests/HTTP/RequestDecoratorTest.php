<?php

namespace Sabre\HTTP;

class RequestDecoratorTest extends \PHPUnit_Framework_TestCase {

    protected $inner;
    protected $outer;

    function setUp(): void {

        $this->inner = new Request();
        $this->outer = new RequestDecorator($this->inner);

    }

    function testMethod(): void {

        $this->outer->setMethod('FOO');
        $this->assertEquals('FOO', $this->inner->getMethod());
        $this->assertEquals('FOO', $this->outer->getMethod());

    }

    function testUrl(): void {

        $this->outer->setUrl('/foo');
        $this->assertEquals('/foo', $this->inner->getUrl());
        $this->assertEquals('/foo', $this->outer->getUrl());

    }

    function testAbsoluteUrl(): void {

        $this->outer->setAbsoluteUrl('http://example.org/foo');
        $this->assertEquals('http://example.org/foo', $this->inner->getAbsoluteUrl());
        $this->assertEquals('http://example.org/foo', $this->outer->getAbsoluteUrl());

    }

    function testBaseUrl(): void {

        $this->outer->setBaseUrl('/foo');
        $this->assertEquals('/foo', $this->inner->getBaseUrl());
        $this->assertEquals('/foo', $this->outer->getBaseUrl());

    }

    function testPath(): void {

        $this->outer->setBaseUrl('/foo');
        $this->outer->setUrl('/foo/bar');
        $this->assertEquals('bar', $this->inner->getPath());
        $this->assertEquals('bar', $this->outer->getPath());

    }

    function testQueryParams(): void {

        $this->outer->setUrl('/foo?a=b&c=d&e');
        $expected = [
            'a' => 'b',
            'c' => 'd',
            'e' => null,
        ];

        $this->assertEquals($expected, $this->inner->getQueryParameters());
        $this->assertEquals($expected, $this->outer->getQueryParameters());

    }

    function testPostData(): void {

        $postData = [
            'a' => 'b',
            'c' => 'd',
            'e' => null,
        ];

        $this->outer->setPostData($postData);
        $this->assertEquals($postData, $this->inner->getPostData());
        $this->assertEquals($postData, $this->outer->getPostData());

    }


    function testServerData(): void {

        $serverData = [
            'HTTPS' => 'On',
        ];

        $this->outer->setRawServerData($serverData);
        $this->assertEquals('On', $this->inner->getRawServerValue('HTTPS'));
        $this->assertEquals('On', $this->outer->getRawServerValue('HTTPS'));

        $this->assertNull($this->inner->getRawServerValue('FOO'));
        $this->assertNull($this->outer->getRawServerValue('FOO'));
    }

    function testToString(): void {

        $this->inner->setMethod('POST');
        $this->inner->setUrl('/foo/bar/');
        $this->inner->setBody('foo');
        $this->inner->setHeader('foo', 'bar');

        $this->assertEquals((string)$this->inner, (string)$this->outer);

    }

}
