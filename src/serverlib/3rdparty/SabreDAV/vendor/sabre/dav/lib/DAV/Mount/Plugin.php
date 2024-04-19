<?php

namespace Sabre\DAV\Mount;

use Sabre\DAV;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

/**
 * This plugin provides support for RFC4709: Mounting WebDAV servers
 *
 * Simply append ?mount to any collection to generate the davmount response.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Plugin extends DAV\ServerPlugin {

    /**
     * Reference to Server class
     *
     * @var Sabre\DAV\Server
     */
    protected $server;

    /**
     * Initializes the plugin and registers event handles
     *
     * @param DAV\Server $server
     * @return void
     */
    function initialize(DAV\Server $server): void {

        $this->server = $server;
        $this->server->on('method:GET', [$this, 'httpGet'], 90);

    }

    /**
     *
     * 'beforeMethod' event handles. This event handles intercepts GET requests ending
     * with ?mount
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     *
     * @return false|null
     */
    function httpGet(RequestInterface $request, ResponseInterface $response) {

        $queryParams = $request->getQueryParameters();
        if (!array_key_exists('mount', $queryParams)) return;

        $currentUri = $request->getAbsoluteUrl();

        // Stripping off everything after the ?
        list($currentUri) = explode('?', $currentUri);

        $this->davMount($response, $currentUri);

        // Returning false to break the event chain
        return false;

    }

    /**
     * Generates the davmount response
     *
     * @param ResponseInterface $response
     * @param string $uri absolute uri
     * @return void
     */
    function davMount(ResponseInterface $response, $uri): void {

        $response->setStatus(200);
        $response->setHeader('Content-Type', 'application/davmount+xml');
        ob_start();
        echo '<?xml version="1.0"?>', "\n";
        echo "<dm:mount xmlns:dm=\"http://purl.org/NET/webdav/mount\">\n";
        echo "  <dm:url>", htmlspecialchars($uri, ENT_NOQUOTES, 'UTF-8'), "</dm:url>\n";
        echo "</dm:mount>";
        $response->setBody(ob_get_clean());

    }


}
