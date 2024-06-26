<?php

namespace Sabre\CalDAV;

use Sabre\DAVACL;

class CalendarHomeNotificationsTest extends \PHPUnit_Framework_TestCase {

    protected $backend;

    function testGetChildrenNoSupport(): void {

        $backend = new Backend\Mock();
        $calendarHome = new CalendarHome($backend,['uri' => 'principals/user']);

        $this->assertEquals(
            [],
            $calendarHome->getChildren()
        );

    }

    /**
     * @expectedException \Sabre\DAV\Exception\NotFound
     */
    function testGetChildNoSupport(): void {

        $backend = new Backend\Mock();
        $calendarHome = new CalendarHome($backend,['uri' => 'principals/user']);
        $calendarHome->getChild('notifications');

    }

    function testGetChildren(): void {

        $backend = new Backend\MockSharing();
        $calendarHome = new CalendarHome($backend,['uri' => 'principals/user']);

        $result = $calendarHome->getChildren();
        $this->assertEquals('notifications', $result[0]->getName());

    }

    function testGetChild(): void {

        $backend = new Backend\MockSharing();
        $calendarHome = new CalendarHome($backend,['uri' => 'principals/user']);
        $result = $calendarHome->getChild('notifications');
        $this->assertEquals('notifications', $result->getName());

    }

}
