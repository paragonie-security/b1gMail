<?php

namespace Sabre\DAV;
use Sabre\HTTP;

require_once 'Sabre/DAV/AbstractServer.php';

class ServerEventsTest extends AbstractServer {

    private $tempPath;

    private $exception;

    function testAfterBind(): void {

        $this->server->on('afterBind', [$this,'afterBindHandler']);
        $newPath = 'afterBind';

        $this->tempPath = '';
        $this->server->createFile($newPath,'body');
        $this->assertEquals($newPath, $this->tempPath);

    }

    function afterBindHandler($path): void {

       $this->tempPath = $path;

    }

    function testAfterResponse(): void {

        $mock = $this->getMock('stdClass', array('afterResponseCallback'));
        $mock->expects($this->once())->method('afterResponseCallback');

        $this->server->on('afterResponse', [$mock, 'afterResponseCallback']);

        $this->server->httpRequest = HTTP\Sapi::createFromServerArray(array(
            'REQUEST_METHOD'    => 'GET',
            'REQUEST_URI'       => '/test.txt',
        ));

        $this->server->exec();

    }

    function testBeforeBindCancel(): void {

        $this->server->on('beforeBind', [$this,'beforeBindCancelHandler']);
        $this->assertFalse($this->server->createFile('bla','body'));

        // Also testing put()
        $req = HTTP\Sapi::createFromServerArray(array(
            'REQUEST_METHOD' => 'PUT',
            'REQUEST_URI' => '/barbar',
        ));

        $this->server->httpRequest = $req;
        $this->server->exec();

        $this->assertEquals('',$this->server->httpResponse->status);

    }

    function beforeBindCancelHandler() {

        return false;

    }

    function testException(): void {

        $this->server->on('exception', [$this, 'exceptionHandler']);

        $req = HTTP\Sapi::createFromServerArray(array(
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/not/exisitng',
        ));
        $this->server->httpRequest = $req;
        $this->server->exec();

        $this->assertInstanceOf('Sabre\\DAV\\Exception\\NotFound', $this->exception);

    }

    function exceptionHandler(Exception $exception): void {

        $this->exception = $exception;

    }

    function testMethod(): void {

        $k = 1;
        $this->server->on('method', function() use (&$k) {

            $k+=1;

            return false;

        });
        $this->server->on('method', function() use (&$k) {

            $k+=2;

            return false;

        });

        $this->server->invokeMethod(
            new HTTP\Request('BLABLA', '/'),
            new HTTP\Response(),
            false
        );

        $this->assertEquals(2, $k);


    }

}
