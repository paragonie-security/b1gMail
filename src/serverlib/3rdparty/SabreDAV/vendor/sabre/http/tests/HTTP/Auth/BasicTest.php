<?php

namespace Sabre\HTTP\Auth;

use Sabre\HTTP\Request;
use Sabre\HTTP\Response;

class BasicTest extends \PHPUnit_Framework_TestCase {

    function testGetCredentials(): void {

        $request = new Request('GET', '/', [
            'Authorization' => 'Basic ' . base64_encode('user:pass:bla')
        ]);

        $basic = new Basic('Dagger', $request, new Response());

        $this->assertEquals([
            'user',
            'pass:bla',
        ], $basic->getCredentials());

    }

    function testGetInvalidCredentialsColonMissing(): void {

        $request = new Request('GET', '/', [
            'Authorization' => 'Basic ' . base64_encode('userpass')
        ]);

        $basic = new Basic('Dagger', $request, new Response());

        $this->assertNull($basic->getCredentials());

    }

    function testGetCredentialsNoheader(): void {

        $request = new Request('GET', '/', []);
        $basic = new Basic('Dagger', $request, new Response());

        $this->assertNull($basic->getCredentials());

    }

    function testGetCredentialsNotBasic(): void {

        $request = new Request('GET', '/', [
            'Authorization' => 'QBasic ' . base64_encode('user:pass:bla')
        ]);
        $basic = new Basic('Dagger', $request, new Response());

        $this->assertNull($basic->getCredentials());

    }

    function testRequireLogin(): void {

        $response = new Response();
        $basic = new Basic('Dagger', new Request(), $response);

        $basic->requireLogin();

        $this->assertEquals('Basic realm="Dagger"', $response->getHeader('WWW-Authenticate'));
        $this->assertEquals(401, $response->getStatus());

    }

}
