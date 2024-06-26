<?php

namespace Sabre\CalDAV;

use
    Sabre\DAV,
    Sabre\DAV\MkCol,
    Sabre\DAVACL;


class CalendarHomeTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Sabre\CalDAV\CalendarHome
     */
    protected $usercalendars;
    /**
     * @var Sabre\CalDAV\Backend\PDO
     */
    protected $backend;

    function setup(): void {

        if (!SABRE_HASSQLITE) $this->markTestSkipped('SQLite driver is not available');
        $this->backend = TestUtil::getBackend();
        $this->usercalendars = new CalendarHome($this->backend, array(
            'uri' => 'principals/user1'
        ));

    }

    function testSimple(): void {

        $this->assertEquals('user1',$this->usercalendars->getName());

    }

    /**
     * @expectedException Sabre\DAV\Exception\NotFound
     * @depends testSimple
     */
    function testGetChildNotFound(): void {

        $this->usercalendars->getChild('randomname');

    }

    function testChildExists(): void {

        $this->assertFalse($this->usercalendars->childExists('foo'));
        $this->assertTrue($this->usercalendars->childExists('UUID-123467'));

    }

    function testGetOwner(): void {

        $this->assertEquals('principals/user1', $this->usercalendars->getOwner());

    }

    function testGetGroup(): void {

        $this->assertNull($this->usercalendars->getGroup());

    }

    function testGetACL(): void {

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
            array(
                'privilege' => '{DAV:}read',
                'principal' => 'principals/user1/calendar-proxy-write',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}write',
                'principal' => 'principals/user1/calendar-proxy-write',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}read',
                'principal' => 'principals/user1/calendar-proxy-read',
                'protected' => true,
            ),
        );
        $this->assertEquals($expected, $this->usercalendars->getACL());

    }

    /**
     * @expectedException Sabre\DAV\Exception\MethodNotAllowed
     */
    function testSetACL(): void {

        $this->usercalendars->setACL(array());

    }

    /**
     * @expectedException Sabre\DAV\Exception\Forbidden
     * @depends testSimple
     */
    function testSetName(): void {

        $this->usercalendars->setName('bla');

    }

    /**
     * @expectedException Sabre\DAV\Exception\Forbidden
     * @depends testSimple
     */
    function testDelete(): void {

        $this->usercalendars->delete();

    }

    /**
     * @depends testSimple
     */
    function testGetLastModified(): void {

        $this->assertNull($this->usercalendars->getLastModified());

    }

    /**
     * @expectedException Sabre\DAV\Exception\MethodNotAllowed
     * @depends testSimple
     */
    function testCreateFile(): void {

        $this->usercalendars->createFile('bla');

    }


    /**
     * @expectedException Sabre\DAV\Exception\MethodNotAllowed
     * @depends testSimple
     */
    function testCreateDirectory(): void {

        $this->usercalendars->createDirectory('bla');

    }

    /**
     * @depends testSimple
     */
    function testCreateExtendedCollection(): void {

        $mkCol = new MkCol(
            ['{DAV:}collection', '{urn:ietf:params:xml:ns:caldav}calendar'],
            []
        );
        $result = $this->usercalendars->createExtendedCollection('newcalendar', $mkCol);
        $this->assertNull($result);
        $cals = $this->backend->getCalendarsForUser('principals/user1');
        $this->assertEquals(3,count($cals));

    }

    /**
     * @expectedException Sabre\DAV\Exception\InvalidResourceType
     * @depends testSimple
     */
    function testCreateExtendedCollectionBadResourceType(): void {

        $mkCol = new MkCol(
            ['{DAV:}collection', '{DAV:}blabla'],
            []
        );
        $this->usercalendars->createExtendedCollection('newcalendar', $mkCol);

    }

    /**
     * @expectedException Sabre\DAV\Exception\InvalidResourceType
     * @depends testSimple
     */
    function testCreateExtendedCollectionNotACalendar(): void {

        $mkCol = new MkCol(
            ['{DAV:}collection'],
            []
        );
        $this->usercalendars->createExtendedCollection('newcalendar', $mkCol);

    }

    function testGetSupportedPrivilegesSet(): void {

        $this->assertNull($this->usercalendars->getSupportedPrivilegeSet());

    }

    /**
     * @expectedException Sabre\DAV\Exception\NotImplemented
     */
    function testShareReplyFail(): void {

        $this->usercalendars->shareReply('uri', SharingPlugin::STATUS_DECLINED, 'curi', '1');

    }

}
