<?php

namespace Sabre\CalDAV\Notifications;

use Sabre\CalDAV;

class NodeTest extends \PHPUnit_Framework_TestCase {

    protected $systemStatus;
    protected $caldavBackend;

    function getInstance() {

        $principalUri = 'principals/user1';

        $this->systemStatus = new CalDAV\Xml\Notification\SystemStatus(1,'"1"');

        $this->caldavBackend = new CalDAV\Backend\MockSharing([], [], [
            'principals/user1' => [
                $this->systemStatus
            ]
        ]);

        $node = new Node($this->caldavBackend, 'principals/user1', $this->systemStatus);
        return $node;

    }

    function testGetId(): void {

        $node = $this->getInstance();
        $this->assertEquals($this->systemStatus->getId() . '.xml', $node->getName());

    }

    function testGetEtag(): void {

        $node = $this->getInstance();
        $this->assertEquals('"1"', $node->getETag());

    }

    function testGetNotificationType(): void {

        $node = $this->getInstance();
        $this->assertEquals($this->systemStatus, $node->getNotificationType());

    }

    function testDelete(): void {

        $node = $this->getInstance();
        $node->delete();
        $this->assertEquals(array(), $this->caldavBackend->getNotificationsForPrincipal('principals/user1'));

    }

    function testGetGroup(): void {

        $node = $this->getInstance();
        $this->assertNull($node->getGroup());

    }

    function testGetACL(): void {

        $node = $this->getInstance();
        $expected = array(
            array(
                'privilege' => '{DAV:}read',
                'principal' => 'principals/user1',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}write',
                'principal' => 'principals/user1',
                'protected' => true,
            ),
        );

        $this->assertEquals($expected, $node->getACL());

    }

    /**
     * @expectedException Sabre\DAV\Exception\NotImplemented
     */
    function testSetACL(): void {

        $node = $this->getInstance();
        $node->setACL(array());

    }

    function testGetSupportedPrivilegeSet(): void {

        $node = $this->getInstance();
        $this->assertNull($node->getSupportedPrivilegeSet());

    }
}
