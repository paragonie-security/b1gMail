<?php

namespace Sabre\DAV\Auth\Backend;

use Sabre\DAV;
use Sabre\HTTP;

class AbstractDigestTest extends \PHPUnit_Framework_TestCase {

    function testCheckNoHeaders(): void {

        $request = new HTTP\Request();
        $response = new HTTP\Response();

        $backend = new AbstractDigestMock();
        $this->assertFalse(
            $backend->check($request, $response)[0]
        );

    }

    function testCheckBadGetUserInfoResponse(): void {

        $header = 'username=null, realm=myRealm, nonce=12345, uri=/, response=HASH, opaque=1, qop=auth, nc=1, cnonce=1';
        $request = HTTP\Sapi::createFromServerArray([
            'PHP_AUTH_DIGEST' => $header,
        ]);
        $response = new HTTP\Response();

        $backend = new AbstractDigestMock();
        $this->assertFalse(
            $backend->check($request, $response)[0]
        );

    }

    /**
     * @expectedException Sabre\DAV\Exception
     */
    function testCheckBadGetUserInfoResponse2(): void {

        $header = 'username=array, realm=myRealm, nonce=12345, uri=/, response=HASH, opaque=1, qop=auth, nc=1, cnonce=1';
        $request = HTTP\Sapi::createFromServerArray([
            'PHP_AUTH_DIGEST' => $header,
        ]);

        $response = new HTTP\Response();

        $backend = new AbstractDigestMock();
        $this->assertNull(
            $backend->check($request, $response)
        );

        $backend = new AbstractDigestMock();
        $backend->check($request, $response);

    }

    function testCheckUnknownUser(): void {

        $header = 'username=false, realm=myRealm, nonce=12345, uri=/, response=HASH, opaque=1, qop=auth, nc=1, cnonce=1';
        $request = HTTP\Sapi::createFromServerArray([
            'PHP_AUTH_DIGEST' => $header,
        ]);

        $response = new HTTP\Response();

        $backend = new AbstractDigestMock();
        $this->assertFalse(
            $backend->check($request, $response)[0]
        );

    }

    function testCheckBadPassword(): void {

        $header = 'username=user, realm=myRealm, nonce=12345, uri=/, response=HASH, opaque=1, qop=auth, nc=1, cnonce=1';
        $request = HTTP\Sapi::createFromServerArray([
            'PHP_AUTH_DIGEST' => $header,
            'REQUEST_METHOD'  => 'PUT',
        ]);

        $response = new HTTP\Response();

        $backend = new AbstractDigestMock();
        $this->assertFalse(
            $backend->check($request, $response)[0]
        );

    }

    function testCheck(): void {

        $digestHash = md5('HELLO:12345:1:1:auth:' . md5('GET:/'));
        $header = 'username=user, realm=myRealm, nonce=12345, uri=/, response='.$digestHash.', opaque=1, qop=auth, nc=1, cnonce=1';
        $request = HTTP\Sapi::createFromServerArray(array(
            'REQUEST_METHOD'  => 'GET',
            'PHP_AUTH_DIGEST' => $header,
            'REQUEST_URI'     => '/',
        ));

        $response = new HTTP\Response();

        $backend = new AbstractDigestMock();
        $this->assertEquals(
            [true, 'principals/user'],
            $backend->check($request, $response)
        );

    }

    function testRequireAuth(): void {

        $request = new HTTP\Request();
        $response = new HTTP\Response();

        $backend = new AbstractDigestMock();
        $backend->setRealm('writing unittests on a saturday night');
        $backend->challenge($request, $response);

        $this->assertStringStartsWith(
            'Digest realm="writing unittests on a saturday night"',
            $response->getHeader('WWW-Authenticate')
        );

    }

}


class AbstractDigestMock extends AbstractDigest {

    /**
     * @return array|false|null|string
     *
     * @psalm-return 'HELLO'|array<never, never>|false|null
     */
    function getDigestHash($realm, $userName) {

        switch($userName) {
            case 'null' : return null;
            case 'false' : return false;
            case 'array' : return array();
            case 'user'  : return 'HELLO';
        }

    }

}
