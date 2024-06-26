<?php

namespace Sabre\CardDAV;

use Sabre\DAV;
use Sabre\VObject;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

/**
 * VCF Exporter
 *
 * This plugin adds the ability to export entire address books as .vcf files.
 * This is useful for clients that don't support CardDAV yet. They often do
 * support vcf files.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @author Thomas Tanghus (http://tanghus.net/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class VCFExportPlugin extends DAV\ServerPlugin {

    /**
     * Reference to Server class
     *
     * @var Sabre\DAV\Server
     */
    protected $server;

    /**
     * Initializes the plugin and registers event handlers
     *
     * @param DAV\Server $server
     * @return void
     */
    function initialize(DAV\Server $server): void {

        $this->server = $server;
        $this->server->on('method:GET', [$this, 'httpGet'], 90);
        $server->on('browserButtonActions', function($path, $node, &$actions) {
            if ($node instanceof IAddressBook) {
                $actions .= '<a href="' . htmlspecialchars($path, ENT_QUOTES, 'UTF-8') . '?export"><span class="oi" data-glyph="book"></span></a>';
            }
        });
    }

    /**
     *
     * Intercepts GET requests on addressbook urls ending with ?export.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     *
     * @return false|null
     */
    function httpGet(RequestInterface $request, ResponseInterface $response) {

        $queryParams = $request->getQueryParameters();
        if (!array_key_exists('export', $queryParams)) return;

        $path = $request->getPath();

        $node = $this->server->tree->getNodeForPath($path);

        if (!($node instanceof IAddressBook)) return;

        $this->server->transactionType = 'get-addressbook-export';

        // Checking ACL, if available.
        if ($aclPlugin = $this->server->getPlugin('acl')) {
            $aclPlugin->checkPrivileges($path, '{DAV:}read');
        }

        $response->setHeader('Content-Type', 'text/directory');
        $response->setStatus(200);

        $nodes = $this->server->getPropertiesForPath($path, [
            '{' . Plugin::NS_CARDDAV . '}address-data',
        ], 1);

        $response->setBody($this->generateVCF($nodes));

        // Returning false to break the event chain
        return false;

    }

    /**
     * Merges all vcard objects, and builds one big vcf export
     *
     * @param array $nodes
     * @return string
     */
    function generateVCF(array $nodes) {

        $output = "";

        foreach ($nodes as $node) {

            if (!isset($node[200]['{' . Plugin::NS_CARDDAV . '}address-data'])) {
                continue;
            }
            $nodeData = $node[200]['{' . Plugin::NS_CARDDAV . '}address-data'];

            // Parsing this node so VObject can clean up the output.
            $output .=
               VObject\Reader::read($nodeData)->serialize();

        }

        return $output;

    }

    /**
     * Returns a plugin name.
     *
     * Using this name other plugins will be able to access other plugins
     * using \Sabre\DAV\Server::getPlugin
     *
     * @return string
     */
    function getPluginName() {

        return 'vcf-export';

    }

    /**
     * Returns a bunch of meta-data about the plugin.
     *
     * Providing this information is optional, and is mainly displayed by the
     * Browser plugin.
     *
     * The description key in the returned array may contain html and will not
     * be sanitized.
     *
     * @return array
     */
    function getPluginInfo() {

        return [
            'name'        => $this->getPluginName(),
            'description' => 'Adds the ability to export CardDAV addressbooks as a single vCard file.',
            'link'        => 'http://sabre.io/dav/vcf-export-plugin/',
        ];

    }

}
