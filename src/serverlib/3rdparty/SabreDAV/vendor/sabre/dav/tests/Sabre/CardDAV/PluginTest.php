<?php

namespace Sabre\CardDAV;

use Sabre\DAV;
use Sabre\DAV\Xml\Property\Href;

class PluginTest extends AbstractPluginTest {

    function testConstruct(): void {

        $this->assertEquals('{' . Plugin::NS_CARDDAV . '}addressbook', $this->server->resourceTypeMapping['Sabre\\CardDAV\\IAddressBook']);

        $this->assertTrue(in_array('addressbook', $this->plugin->getFeatures()));
        $this->assertEquals('carddav', $this->plugin->getPluginInfo()['name']);

    }

    function testSupportedReportSet(): void {

        $this->assertEquals(array(
            '{' . Plugin::NS_CARDDAV . '}addressbook-multiget',
            '{' . Plugin::NS_CARDDAV . '}addressbook-query',
        ), $this->plugin->getSupportedReportSet('addressbooks/user1/book1'));

    }

    function testSupportedReportSetEmpty(): void {

        $this->assertEquals(array(
        ), $this->plugin->getSupportedReportSet(''));

    }

    function testAddressBookHomeSet(): void {

        $result = $this->server->getProperties('principals/user1', array('{' . Plugin::NS_CARDDAV . '}addressbook-home-set'));

        $this->assertEquals(1, count($result));
        $this->assertTrue(isset($result['{' . Plugin::NS_CARDDAV . '}addressbook-home-set']));
        $this->assertEquals('addressbooks/user1/', $result['{' . Plugin::NS_CARDDAV . '}addressbook-home-set']->getHref());

    }

    function testDirectoryGateway(): void {

        $result = $this->server->getProperties('principals/user1', array('{' . Plugin::NS_CARDDAV . '}directory-gateway'));

        $this->assertEquals(1, count($result));
        $this->assertTrue(isset($result['{' . Plugin::NS_CARDDAV . '}directory-gateway']));
        $this->assertEquals(array('directory'), $result['{' . Plugin::NS_CARDDAV . '}directory-gateway']->getHrefs());

    }

    function testReportPassThrough(): void {

        $this->assertNull($this->plugin->report('{DAV:}foo', new \DomDocument()));

    }

    function testHTMLActionsPanel(): void {

        $output = '';
        $r = $this->server->emit('onHTMLActionsPanel', [$this->server->tree->getNodeForPath('addressbooks/user1'), &$output]);
        $this->assertFalse($r);

        $this->assertTrue(!!strpos($output,'Display name'));

    }

    function testAddressbookPluginProperties(): void {

        $ns = '{' . Plugin::NS_CARDDAV . '}';
        $propFind = new DAV\PropFind('addressbooks/user1/book1', [
            $ns . 'supported-address-data',
            $ns . 'supported-collation-set',
        ]);
        $node = $this->server->tree->getNodeForPath('addressbooks/user1/book1');
        $this->plugin->propFindEarly($propFind, $node);

        $this->assertInstanceOf(
            'Sabre\\CardDAV\\Xml\\Property\\SupportedAddressData',
            $propFind->get($ns . 'supported-address-data')
        );
        $this->assertInstanceOf(
            'Sabre\\CardDAV\\Xml\\Property\\SupportedCollationSet',
            $propFind->get($ns . 'supported-collation-set')
        );


    }

    function testGetTransform(): void {

        $request = new \Sabre\HTTP\Request('GET', '/addressbooks/user1/book1/card1', ['Accept: application/vcard+json']);
        $response = new \Sabre\HTTP\ResponseMock();
        $this->server->invokeMethod($request, $response);

        $this->assertEquals(200, $response->getStatus());

    }

}
