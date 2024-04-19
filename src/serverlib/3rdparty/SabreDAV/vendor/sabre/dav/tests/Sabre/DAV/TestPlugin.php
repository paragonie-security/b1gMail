<?php

namespace Sabre\DAV;

use
    Sabre\HTTP\RequestInterface,
    Sabre\HTTP\ResponseInterface;

class TestPlugin extends ServerPlugin {

    public $beforeMethod;

    function getFeatures() {

        return ['drinking'];

    }

    function getHTTPMethods($uri) {

        return ['BEER','WINE'];

    }

    function initialize(Server $server): void {

        $server->on('beforeMethod', [$this,'beforeMethod']);

    }

    /**
     * @return true
     */
    function beforeMethod(RequestInterface $request, ResponseInterface $response): bool {

        $this->beforeMethod = $request->getMethod();
        return true;

    }

}
