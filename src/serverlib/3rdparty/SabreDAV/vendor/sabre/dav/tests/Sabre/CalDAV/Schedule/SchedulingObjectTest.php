<?php

namespace Sabre\CalDAV\Schedule;
use Sabre\DAVACL;
use Sabre\CalDAV\Backend;

class SchedulingObjectTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Sabre\CalDAV\Backend_PDO
     */
    protected $backend;
    /**
     * @var Sabre\CalDAV\Calendar
     */
    protected $calendar;
    protected $principalBackend;

    protected $data;
    protected $data2;

    function setup(): void {

        if (!SABRE_HASSQLITE) $this->markTestSkipped('SQLite driver is not available');
        $this->backend = new Backend\MockScheduling();

        $this->data = <<<ICS
BEGIN:VCALENDAR
METHOD:REQUEST
BEGIN:VEVENT
SEQUENCE:1
END:VEVENT
END:VCALENDAR
ICS;
        $this->data = <<<ICS
BEGIN:VCALENDAR
METHOD:REQUEST
BEGIN:VEVENT
SEQUENCE:2
END:VEVENT
END:VCALENDAR
ICS;

        $this->inbox = new Inbox($this->backend, 'principals/user1');
        $this->inbox->createFile('item1.ics', $this->data);

    }

    function teardown(): void {

        unset($this->inbox);
        unset($this->backend);

    }

    function testSetup(): void {

        $children = $this->inbox->getChildren();
        $this->assertTrue($children[0] instanceof SchedulingObject);

        $this->assertInternalType('string',$children[0]->getName());
        $this->assertInternalType('string',$children[0]->get());
        $this->assertInternalType('string',$children[0]->getETag());
        $this->assertEquals('text/calendar; charset=utf-8', $children[0]->getContentType());

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testInvalidArg1(): void {

        $obj = new SchedulingObject(
            new Backend\MockScheduling(array(),array()),
            array(),
            array()
        );

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testInvalidArg2(): void {

        $obj = new SchedulingObject(
            new Backend\MockScheduling(array(),array()),
            array(),
            array('calendarid' => '1')
        );

    }

    /**
     * @depends testSetup
     * @expectedException \Sabre\DAV\Exception\MethodNotAllowed
     */
    function testPut(): void {

        $children = $this->inbox->getChildren();
        $this->assertTrue($children[0] instanceof SchedulingObject);

        $children[0]->put('');

    }

    /**
     * @depends testSetup
     */
    function testDelete(): void {

        $children = $this->inbox->getChildren();
        $this->assertTrue($children[0] instanceof SchedulingObject);

        $obj = $children[0];
        $obj->delete();

        $children2 =  $this->inbox->getChildren();
        $this->assertEquals(count($children)-1, count($children2));

    }

    /**
     * @depends testSetup
     */
    function testGetLastModified(): void {

        $children = $this->inbox->getChildren();
        $this->assertTrue($children[0] instanceof SchedulingObject);

        $obj = $children[0];

        $lastMod = $obj->getLastModified();
        $this->assertTrue(is_int($lastMod) || ctype_digit($lastMod) || is_null($lastMod));

    }

    /**
     * @depends testSetup
     */
    function testGetSize(): void {

        $children = $this->inbox->getChildren();
        $this->assertTrue($children[0] instanceof SchedulingObject);

        $obj = $children[0];

        $size = $obj->getSize();
        $this->assertInternalType('int', $size);

    }

    function testGetOwner(): void {

        $children = $this->inbox->getChildren();
        $this->assertTrue($children[0] instanceof SchedulingObject);

        $obj = $children[0];
        $this->assertEquals('principals/user1', $obj->getOwner());

    }

    function testGetGroup(): void {

        $children = $this->inbox->getChildren();
        $this->assertTrue($children[0] instanceof SchedulingObject);

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

        $children = $this->inbox->getChildren();
        $this->assertTrue($children[0] instanceof SchedulingObject);

        $obj = $children[0];
        $this->assertEquals($expected, $obj->getACL());

    }

    function testDefaultACL(): void {

        $backend = new Backend\MockScheduling([], []);
        $calendarObject = new SchedulingObject($backend, ['calendarid' => 1, 'uri' => 'foo', 'principaluri' => 'principals/user1' ]);
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

        $children = $this->inbox->getChildren();
        $this->assertTrue($children[0] instanceof SchedulingObject);

        $obj = $children[0];
        $obj->setACL(array());

    }

    function testGet(): void {

        $children = $this->inbox->getChildren();
        $this->assertTrue($children[0] instanceof SchedulingObject);

        $obj = $children[0];

        $this->assertEquals($this->data, $obj->get());

    }

    function testGetRefetch(): void {

        $backend = new Backend\MockScheduling();
        $backend->createSchedulingObject('principals/user1', 'foo', 'foo'); 

        $obj = new SchedulingObject($backend, array( 
            'calendarid' => 1,
            'uri' => 'foo',
            'principaluri' => 'principals/user1',
        ));

        $this->assertEquals('foo', $obj->get());

    }

    function testGetEtag1(): void {

        $objectInfo = array(
            'calendardata' => 'foo',
            'uri' => 'foo',
            'etag' => 'bar',
            'calendarid' => 1
        );

        $backend = new Backend\MockScheduling(array(), array());
        $obj = new SchedulingObject($backend, $objectInfo);

        $this->assertEquals('bar', $obj->getETag());

    }

    function testGetEtag2(): void {

        $objectInfo = array(
            'calendardata' => 'foo',
            'uri' => 'foo',
            'calendarid' => 1
        );

        $backend = new Backend\MockScheduling(array(), array());
        $obj = new SchedulingObject($backend, $objectInfo);

        $this->assertEquals('"' . md5('foo') . '"', $obj->getETag());

    }

    function testGetSupportedPrivilegesSet(): void {

        $objectInfo = array(
            'calendardata' => 'foo',
            'uri' => 'foo',
            'calendarid' => 1
        );

        $backend = new Backend\MockScheduling(array(), array());
        $obj = new SchedulingObject($backend, $objectInfo);
        $this->assertNull($obj->getSupportedPrivilegeSet());

    }

    function testGetSize1(): void {

        $objectInfo = array(
            'calendardata' => 'foo',
            'uri' => 'foo',
            'calendarid' => 1
        );

        $backend = new Backend\MockScheduling(array(), array());
        $obj = new SchedulingObject($backend, $objectInfo);
        $this->assertEquals(3, $obj->getSize());

    }

    function testGetSize2(): void {

        $objectInfo = array(
            'uri' => 'foo',
            'calendarid' => 1,
            'size' => 4,
        );

        $backend = new Backend\MockScheduling(array(), array());
        $obj = new SchedulingObject($backend, $objectInfo);
        $this->assertEquals(4, $obj->getSize());

    }

    function testGetContentType(): void {

        $objectInfo = array(
            'uri' => 'foo',
            'calendarid' => 1,
        );

        $backend = new Backend\MockScheduling(array(), array());
        $obj = new SchedulingObject($backend, $objectInfo);
        $this->assertEquals('text/calendar; charset=utf-8', $obj->getContentType());

    }

    function testGetContentType2(): void {

        $objectInfo = array(
            'uri' => 'foo',
            'calendarid' => 1,
            'component' => 'VEVENT',
        );

        $backend = new Backend\MockScheduling(array(), array());
        $obj = new SchedulingObject($backend, $objectInfo);
        $this->assertEquals('text/calendar; charset=utf-8; component=VEVENT', $obj->getContentType());

    }
    function testGetACL2(): void {

        $objectInfo = array(
            'uri' => 'foo',
            'calendarid' => 1,
            'acl' => [],
        );

        $backend = new Backend\MockScheduling(array(), array());
        $obj = new SchedulingObject($backend, $objectInfo);
        $this->assertEquals([], $obj->getACL());

    }
}
