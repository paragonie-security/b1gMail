<?php

namespace Sabre\CalDAV\Notifications;

use Sabre\CalDAV;

class CollectionTest extends \PHPUnit_Framework_TestCase {

    protected $caldavBackend;
    protected $principalUri;
    protected $notification;

    function getInstance() {

        $this->principalUri = 'principals/user1';

        $this->notification = new CalDAV\Xml\Notification\SystemStatus(1,'"1"');

        $this->caldavBackend = new CalDAV\Backend\MockSharing(array(),array(), array(
            'principals/user1' => array(
                $this->notification
            )
        )); 

        return new Collection($this->caldavBackend, $this->principalUri);

    }

    function testGetChildren(): void {

        $col = $this->getInstance();
        $this->assertEquals('notifications', $col->getName());

        $this->assertEquals(array(
            new Node($this->caldavBackend, $this->principalUri, $this->notification)
        ), $col->getChildren()); 

    }

    function testGetOwner(): void {

        $col = $this->getInstance();
        $this->assertEquals('principals/user1', $col->getOwner());

    }

    function testGetGroup(): void {

        $col = $this->getInstance();
        $this->assertNull($col->getGroup());

    }

    function testGetACL(): void {

        $col = $this->getInstance();
        $expected = array(
            array(
                'privilege' => '{DAV:}read',
                'principal' => $this->principalUri,
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}write',
                'principal' => $this->principalUri,
                'protected' => true,
            ),
        );

        $this->assertEquals($expected, $col->getACL());

    }

    /**
     * @expectedException Sabre\DAV\Exception\NotImplemented
     */
    function testSetACL(): void {

        $col = $this->getInstance();
        $col->setACL(array());

    }

    function testGetSupportedPrivilegeSet(): void {

        $col = $this->getInstance();
        $this->assertNull($col->getSupportedPrivilegeSet());

    }
}
