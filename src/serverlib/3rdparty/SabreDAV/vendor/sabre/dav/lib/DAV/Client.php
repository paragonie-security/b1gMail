<?php

namespace Sabre\DAV;

use Sabre\HTTP;

/**
 * SabreDAV DAV client
 *
 * This client wraps around Curl to provide a convenient API to a WebDAV
 * server.
 *
 * NOTE: This class is experimental, it's api will likely change in the future.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Client extends HTTP\Client {

    /**
     * The xml service.
     *
     * Uset this service to configure the property and namespace maps.
     *
     * @var mixed
     */
    public $xml;

    /**
     * The elementMap
     *
     * This property is linked via reference to $this->xml->elementMap.
     * It's deprecated as of version 3.0.0, and should no longer be used.
     *
     * @deprecated
     * @var array
     */
    public $propertyMap = [];

    /**
     * Base URI
     *
     * This URI will be used to resolve relative urls.
     *
     * @var string
     */
    protected $baseUri;

    /**
     * Basic authentication
     */
    const AUTH_BASIC = 1;

    /**
     * Digest authentication
     */
    const AUTH_DIGEST = 2;

    /**
     * NTLM authentication
     */
    const AUTH_NTLM = 4;

    /**
     * Identity encoding, which basically does not nothing.
     */
    const ENCODING_IDENTITY = 1;

    /**
     * Deflate encoding
     */
    const ENCODING_DEFLATE = 2;

    /**
     * Gzip encoding
     */
    const ENCODING_GZIP = 4;

    /**
     * Sends all encoding headers.
     */
    const ENCODING_ALL = 7;

    /**
     * Content-encoding
     *
     * @var int
     */
    protected $encoding = self::ENCODING_IDENTITY;

    /**
     * Constructor
     *
     * Settings are provided through the 'settings' argument. The following
     * settings are supported:
     *
     *   * baseUri
     *   * userName (optional)
     *   * password (optional)
     *   * proxy (optional)
     *   * authType (optional)
     *   * encoding (optional)
     *
     *  authType must be a bitmap, using self::AUTH_BASIC, self::AUTH_DIGEST
     *  and self::AUTH_NTLM. If you know which authentication method will be
     *  used, it's recommended to set it, as it will save a great deal of
     *  requests to 'discover' this information.
     *
     *  Encoding is a bitmap with one of the ENCODING constants.
     *
     * @param array $settings
     */
    function __construct(array $settings) {

        if (!isset($settings['baseUri'])) {
            throw new \InvalidArgumentException('A baseUri must be provided');
        }

        parent::__construct();

        $this->baseUri = $settings['baseUri'];

        if (isset($settings['proxy'])) {
            $this->addCurlSetting(CURLOPT_PROXY, $settings['proxy']);
        }

        if (isset($settings['userName'])) {
            $userName = $settings['userName'];
            $password = isset($settings['password']) ? $settings['password'] : '';

            if (isset($settings['authType'])) {
                $curlType = 0;
                if ($settings['authType'] & self::AUTH_BASIC) {
                    $curlType |= CURLAUTH_BASIC;
                }
                if ($settings['authType'] & self::AUTH_DIGEST) {
                    $curlType |= CURLAUTH_DIGEST;
                }
                if ($settings['authType'] & self::AUTH_NTLM) {
                    $curlType |= CURLAUTH_NTLM;
                }
            } else {
                $curlType = CURLAUTH_BASIC | CURLAUTH_DIGEST;
            }

            $this->addCurlSetting(CURLOPT_HTTPAUTH, $curlType);
            $this->addCurlSetting(CURLOPT_USERPWD, $userName . ':' . $password);

        }

        if (isset($settings['encoding'])) {
            $encoding = $settings['encoding'];

            $encodings = [];
            if ($encoding & self::ENCODING_IDENTITY) {
                $encodings[] = 'identity';
            }
            if ($encoding & self::ENCODING_DEFLATE) {
                $encodings[] = 'deflate';
            }
            if ($encoding & self::ENCODING_GZIP) {
                $encodings[] = 'gzip';
            }
            $this->addCurlSetting(CURLOPT_ENCODING, implode(',', $encodings));
        }

        $this->xml = new Xml\Service();
        // BC
        $this->propertyMap = & $this->xml->elementMap;

    }

    /**
     * Does a PROPFIND request
     *
     * The list of requested properties must be specified as an array, in clark
     * notation.
     *
     * The returned array will contain a list of filenames as keys, and
     * properties as values.
     *
     * The properties array will contain the list of properties. Only properties
     * that are actually returned from the server (without error) will be
     * returned, anything else is discarded.
     *
     * Depth should be either 0 or 1. A depth of 1 will cause a request to be
     * made to the server to also return all child resources.
     *
     * @param string $url
     * @param array $properties
     * @param int $depth
     * @return array
     */
    function propFind($url, array $properties, $depth = 0) {

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $root = $dom->createElementNS('DAV:', 'd:propfind');
        $prop = $dom->createElement('d:prop');

        foreach ($properties as $property) {

            list(
                $namespace,
                $elementName
            ) = \Sabre\Xml\Service::parseClarkNotation($property);

            if ($namespace === 'DAV:') {
                $element = $dom->createElement('d:' . $elementName);
            } else {
                $element = $dom->createElementNS($namespace, 'x:' . $elementName);
            }

            $prop->appendChild($element);
        }

        $dom->appendChild($root)->appendChild($prop);
        $body = $dom->saveXML();

        $url = $this->getAbsoluteUrl($url);

        $request = new HTTP\Request('PROPFIND', $url, [
            'Depth'        => $depth,
            'Content-Type' => 'application/xml'
        ], $body);

        $response = $this->send($request);

        if ((int)$response->getStatus() >= 400) {
            throw new Exception('HTTP error: ' . $response->getStatus());
        }

        $result = $this->parseMultiStatus($response->getBodyAsString());

        // If depth was 0, we only return the top item
        if ($depth === 0) {
            reset($result);
            $result = current($result);
            return isset($result[200]) ? $result[200] : [];
        }

        $newResult = [];
        foreach ($result as $href => $statusList) {

            $newResult[$href] = isset($statusList[200]) ? $statusList[200] : [];

        }

        return $newResult;

    }

    /**
     * Updates a list of properties on the server
     *
     * The list of properties must have clark-notation properties for the keys,
     * and the actual (string) value for the value. If the value is null, an
     * attempt is made to delete the property.
     *
     * @param string $url
     * @param array $properties
     * @return void
     */
    function propPatch($url, array $properties): void {

        $propPatch = new Xml\Request\PropPatch();
        $propPatch->properties = $properties;
        $xml = $this->xml->write(
            '{DAV:}propertyupdate',
            $propPatch
        );

        $url = $this->getAbsoluteUrl($url);
        $request = new HTTP\Request('PROPPATCH', $url, [
            'Content-Type' => 'application/xml',
        ], $xml);
        $this->send($request);
    }





    /**
     * Returns the full url based on the given url (which may be relative). All
     * urls are expanded based on the base url as given by the server.
     *
     * @param string $url
     * @return string
     */
    function getAbsoluteUrl($url) {

        // If the url starts with http:// or https://, the url is already absolute.
        if (preg_match('/^http(s?):\/\//', $url)) {
            return $url;
        }

        // If the url starts with a slash, we must calculate the url based off
        // the root of the base url.
        if (strpos($url, '/') === 0) {
            $parts = parse_url($this->baseUri);
            return $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '') . $url;
        }

        // Otherwise...
        return $this->baseUri . $url;

    }

    /**
     * Parses a WebDAV multistatus response body
     *
     * This method returns an array with the following structure
     *
     * [
     *   'url/to/resource' => [
     *     '200' => [
     *        '{DAV:}property1' => 'value1',
     *        '{DAV:}property2' => 'value2',
     *     ],
     *     '404' => [
     *        '{DAV:}property1' => null,
     *        '{DAV:}property2' => null,
     *     ],
     *   ],
     *   'url/to/resource2' => [
     *      .. etc ..
     *   ]
     * ]
     *
     *
     * @param string $body xml body
     * @return array
     */
    function parseMultiStatus($body) {

        $multistatus = $this->xml->expect('{DAV:}multistatus', $body);

        $result = [];

        foreach ($multistatus->getResponses() as $response) {

            $result[$response->getHref()] = $response->getResponseProperties();

        }

        return $result;

    }

}
