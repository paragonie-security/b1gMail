<?php

namespace Sabre\DAVACL\Exception;

use Sabre\DAV;

class NotRecognizedPrincipalTest extends \PHPUnit_Framework_TestCase {

    function testSerialize(): void {

        $ex = new NotRecognizedPrincipal('message');

        $server = new DAV\Server();
        $dom = new \DOMDocument('1.0','utf-8');
        $root = $dom->createElementNS('DAV:','d:root');
        $dom->appendChild($root);

        $ex->serialize($server, $root);

        $xpaths = array(
            '/d:root' => 1,
            '/d:root/d:recognized-principal' => 1,
        );

        // Reloading because PHP DOM sucks
        $dom2 = new \DOMDocument('1.0', 'utf-8');
        $dom2->loadXML($dom->saveXML());

        $dxpath = new \DOMXPath($dom2);
        $dxpath->registerNamespace('d','DAV:');
        foreach($xpaths as $xpath=>$count) {

            $this->assertEquals($count, $dxpath->query($xpath)->length, 'Looking for : ' . $xpath . ', we could only find ' . $dxpath->query($xpath)->length . ' elements, while we expected ' . $count);

        }

    }

}
