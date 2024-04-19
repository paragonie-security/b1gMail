<?php

namespace Sabre\HTTP\Auth;

use Sabre\HTTP\Request;
use Sabre\HTTP\Response;

class BearerTest extends \PHPUnit_Framework_TestCase {

    function testGetToken(): void {

        $request = new Request('GET', '/', [
            'Authorization' => 'Bearer 12345'
        ]);

        $bearer = new Bearer('Dagger', $request, new Response());

        $this->assertEquals(
            '12345',
            $bearer->getToken()
        );

    }

    function testGetCredentialsNoheader(): void {

        $request = new Request('GET', '/', []);
        $bearer = new Bearer('Dagger', $request, new Response());

        $this->assertNull($bearer->getToken());

    }

    function testGetCredentialsNotBearer(): void {

        $request = new Request('GET', '/', [
            'Authorization' => 'QBearer 12345'
        ]);
        $bearer = new Bearer('Dagger', $request, new Response());

        $this->assertNull($bearer->getToken());

    }

    function testRequireLogin(): void {

        $response = new Response();
        $bearer = new Bearer('Dagger', new Request(), $response);

        $bearer->requireLogin();

        $this->assertEquals('Bearer realm="Dagger"', $response->getHeader('WWW-Authenticate'));
        $this->assertEquals(401, $response->getStatus());

    }

}
