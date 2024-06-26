<?php

namespace Sabre\CalDAV;
use Sabre\DAVACL;

require_once 'Sabre/CalDAV/TestUtil.php';

class CalendarObjectTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Sabre\CalDAV\Backend_PDO
     */
    protected $backend;
    /**
     * @var Sabre\CalDAV\Calendar
     */
    protected $calendar;
    protected $principalBackend;

    function setup(): void {

        if (!SABRE_HASSQLITE) $this->markTestSkipped('SQLite driver is not available');
        $this->backend = TestUtil::getBackend();

        $calendars = $this->backend->getCalendarsForUser('principals/user1');
        $this->assertEquals(2,count($calendars));
        $this->calendar = new Calendar($this->backend, $calendars[0]);

    }

    function teardown(): void {

        unset($this->calendar);
        unset($this->backend);

    }

    function testSetup(): void {

        $children = $this->calendar->getChildren();
        $this->assertTrue($children[0] instanceof CalendarObject);

        $this->assertInternalType('string',$children[0]->getName());
        $this->assertInternalType('string',$children[0]->get());
        $this->assertInternalType('string',$children[0]->getETag());
        $this->assertEquals('text/calendar; charset=utf-8; component=vevent', $children[0]->getContentType());

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testInvalidArg1(): void {

        $obj = new CalendarObject(
            new Backend\Mock(array(),array()),
            array(),
            array()
        );

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testInvalidArg2(): void {

        $obj = new CalendarObject(
            new Backend\Mock(array(),array()),
            array(),
            array('calendarid' => '1')
        );

    }

    /**
     * @depends testSetup
     */
    function testPut(): void {

        $children = $this->calendar->getChildren();
        $this->assertTrue($children[0] instanceof CalendarObject);
        $newData = TestUtil::getTestCalendarData();

        $children[0]->put($newData);
        $this->assertEquals($newData, $children[0]->get());

    }

    /**
     * @depends testSetup
     */
    function testPutStream(): void {

        $children = $this->calendar->getChildren();
        $this->assertTrue($children[0] instanceof CalendarObject);
        $newData = TestUtil::getTestCalendarData();

        $stream = fopen('php://temp','r+');
        fwrite($stream, $newData);
        rewind($stream);
        $children[0]->put($stream);
        $this->assertEquals($newData, $children[0]->get());

    }


    /**
     * @depends testSetup
     */
    function testDelete(): void {

        $children = $this->calendar->getChildren();
        $this->assertTrue($children[0] instanceof CalendarObject);

        $obj = $children[0];
        $obj->delete();

        $children2 =  $this->calendar->getChildren();
        $this->assertEquals(count($children)-1, count($children2));

    }

    /**
     * @depends testSetup
     */
    function testGetLastModified(): void {

        $children = $this->calendar->getChildren();
        $this->assertTrue($children[0] instanceof CalendarObject);

        $obj = $children[0];

        $lastMod = $obj->getLastModified();
        $this->assertTrue(is_int($lastMod) || ctype_digit($lastMod));

    }

    /**
     * @depends testSetup
     */
    function testGetSize(): void {

        $children = $this->calendar->getChildren();
        $this->assertTrue($children[0] instanceof CalendarObject);

        $obj = $children[0];

        $size = $obj->getSize();
        $this->assertInternalType('int', $size);

    }

    function testGetOwner(): void {

        $children = $this->calendar->getChildren();
        $this->assertTrue($children[0] instanceof CalendarObject);

        $obj = $children[0];
        $this->assertEquals('principals/user1', $obj->getOwner());

    }

    function testGetGroup(): void {

        $children = $this->calendar->getChildren();
        $this->assertTrue($children[0] instanceof CalendarObject);

        $obj = $children[0];
        $this->assertNull($obj->getGroup());

    }

    function testGetACL(): void {

        $expected = array(
            array(
                'privilege' => '{DAV:}read',
                'principal' => 'principals/user1',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}read',
                'principal' => 'principals/user1/calendar-proxy-write',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}read',
                'principal' => 'principals/user1/calendar-proxy-read',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}write',
                'principal' => 'principals/user1',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}write',
                'principal' => 'principals/user1/calendar-proxy-write',
                'protected' => true,
            ),
        );

        $children = $this->calendar->getChildren();
        $this->assertTrue($children[0] instanceof CalendarObject);

        $obj = $children[0];
        $this->assertEquals($expected, $obj->getACL());

    }

    function testDefaultACL(): void {

        $backend = new Backend\Mock([], []);
        $calendarObject = new CalendarObject($backend, ['principaluri' => 'principals/user1'], ['calendarid' => 1, 'uri' => 'foo']);
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
        $this->assertEquals($expected, $calendarObject->getACL());


    }

    /**
     * @expectedException Sabre\DAV\Exception\MethodNotAllowed
     */
    function testSetACL(): void {

        $children = $this->calendar->getChildren();
        $this->assertTrue($children[0] instanceof CalendarObject);

        $obj = $children[0];
        $obj->setACL(array());

    }

    function testGet(): void {

        $children = $this->calendar->getChildren();
        $this->assertTrue($children[0] instanceof CalendarObject);

        $obj = $children[0];

            $expected = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Apple Inc.//iCal 4.0.1//EN
CALSCALE:GREGORIAN
BEGIN:VTIMEZONE
TZID:Asia/Seoul
BEGIN:DAYLIGHT
TZOFFSETFROM:+0900
RRULE:FREQ=YEARLY;UNTIL=19880507T150000Z;BYMONTH=5;BYDAY=2SU
DTSTART:19870510T000000
TZNAME:GMT+09:00
TZOFFSETTO:+1000
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+1000
DTSTART:19881009T000000
TZNAME:GMT+09:00
TZOFFSETTO:+0900
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
CREATED:20100225T154229Z
UID:39A6B5ED-DD51-4AFE-A683-C35EE3749627
TRANSP:TRANSPARENT
SUMMARY:Something here
DTSTAMP:20100228T130202Z
DTSTART;TZID=Asia/Seoul:20100223T060000
DTEND;TZID=Asia/Seoul:20100223T070000
ATTENDEE;PARTSTAT=NEEDS-ACTION:mailto:lisa@example.com
SEQUENCE:2
END:VEVENT
END:VCALENDAR";



        $this->assertEquals($expected, $obj->get());

    }

    function testGetRefetch(): void {

        $backend = new Backend\Mock(array(), array(
            1 => array(
                'foo' => array(
                    'calendardata' => 'foo',
                    'uri' => 'foo'
                ),
            )
        ));
        $obj = new CalendarObject($backend, array('id' => 1), array('uri' => 'foo'));

        $this->assertEquals('foo', $obj->get());

    }

    function testGetEtag1(): void {

        $objectInfo = array(
            'calendardata' => 'foo',
            'uri' => 'foo',
            'etag' => 'bar',
            'calendarid' => 1
        );

        $backend = new Backend\Mock(array(), array());
        $obj = new CalendarObject($backend, array(), $objectInfo);

        $this->assertEquals('bar', $obj->getETag());

    }

    function testGetEtag2(): void {

        $objectInfo = array(
            'calendardata' => 'foo',
            'uri' => 'foo',
            'calendarid' => 1
        );

        $backend = new Backend\Mock(array(), array());
        $obj = new CalendarObject($backend, array(), $objectInfo);

        $this->assertEquals('"' . md5('foo') . '"', $obj->getETag());

    }

    function testGetSupportedPrivilegesSet(): void {

        $objectInfo = array(
            'calendardata' => 'foo',
            'uri' => 'foo',
            'calendarid' => 1
        );

        $backend = new Backend\Mock(array(), array());
        $obj = new CalendarObject($backend, array(), $objectInfo);
        $this->assertNull($obj->getSupportedPrivilegeSet());

    }

    function testGetSize1(): void {

        $objectInfo = array(
            'calendardata' => 'foo',
            'uri' => 'foo',
            'calendarid' => 1
        );

        $backend = new Backend\Mock(array(), array());
        $obj = new CalendarObject($backend, array(), $objectInfo);
        $this->assertEquals(3, $obj->getSize());

    }

    function testGetSize2(): void {

        $objectInfo = array(
            'uri' => 'foo',
            'calendarid' => 1,
            'size' => 4,
        );

        $backend = new Backend\Mock(array(), array());
        $obj = new CalendarObject($backend, array(), $objectInfo);
        $this->assertEquals(4, $obj->getSize());

    }
}
