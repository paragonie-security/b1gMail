<?php

namespace Sabre\CalDAV\Schedule;

use Sabre\HTTP\Request;

class ScheduleDeliverTest extends \Sabre\DAVServerTest {

    public $setupCalDAV = true;
    public $setupCalDAVScheduling = true;
    public $setupACL = true;
    public $autoLogin = 'user1';

    public $caldavCalendars = [
        [
            'principaluri' => 'principals/user1',
            'uri' => 'cal',
        ],
        [
            'principaluri' => 'principals/user2',
            'uri' => 'cal',
        ],
    ];

    function setUp(): void {

        $this->calendarObjectUri = '/calendars/user1/cal/object.ics';

        parent::setUp();

    }

    function testNewInvite(): void {

        $newObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->deliver(null, $newObject);
        $this->assertItemsInInbox('user2', 1);

        $expected = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE;SCHEDULE-STATUS=1.2:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->assertVObjEquals(
            $expected,
            $newObject
        );

    }

    function testNewOnWrongCollection(): void {

        $newObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->calendarObjectUri = '/calendars/user1/object.ics';
        $this->deliver(null, $newObject);
        $this->assertItemsInInbox('user2', 0);


    }
    function testNewInviteSchedulingDisabled(): void {

        $newObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->deliver(null, $newObject, true);
        $this->assertItemsInInbox('user2', 0);

    }
    function testUpdatedInvite(): void {

        $newObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;
        $oldObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->deliver($oldObject, $newObject);
        $this->assertItemsInInbox('user2', 1);

        $expected = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE;SCHEDULE-STATUS=1.2:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->assertVObjEquals(
            $expected,
            $newObject
        );


    }
    function testUpdatedInviteSchedulingDisabled(): void {

        $newObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;
        $oldObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->deliver($oldObject, $newObject, true);
        $this->assertItemsInInbox('user2', 0);

    }

    function testUpdatedInviteWrongPath(): void {

        $newObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;
        $oldObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->calendarObjectUri = '/calendars/user1/inbox/foo.ics';
        $this->deliver($oldObject, $newObject);
        $this->assertItemsInInbox('user2', 0);

    }

    function testDeletedInvite(): void {

        $newObject = null;

        $oldObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->deliver($oldObject, $newObject);
        $this->assertItemsInInbox('user2', 1);

    }

    function testDeletedInviteSchedulingDisabled(): void {

        $newObject = null;

        $oldObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->deliver($oldObject, $newObject, true);
        $this->assertItemsInInbox('user2', 0);

    }

    /**
     * A MOVE request will trigger an unbind on a scheduling resource.
     *
     * However, we must not treat it as a cancellation, it just got moved to a
     * different calendar.
     */
    function testUnbindIgnoredOnMove(): void {

        $newObject = null;

        $oldObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;


        $this->server->httpRequest->setMethod('MOVE');
        $this->deliver($oldObject, $newObject);
        $this->assertItemsInInbox('user2', 0);

    }

    function testDeletedInviteWrongUrl(): void {

        $newObject = null;

        $oldObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->calendarObjectUri = '/calendars/user1/inbox/foo.ics';
        $this->deliver($oldObject, $newObject);
        $this->assertItemsInInbox('user2', 0);

    }

    function testReply(): void {

        $oldObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user2.sabredav@sabredav.org
ATTENDEE;PARTSTAT=ACCEPTED:mailto:user2.sabredav@sabredav.org
ATTENDEE:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user3.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $newObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user2.sabredav@sabredav.org
ATTENDEE;PARTSTAT=ACCEPTED:mailto:user2.sabredav@sabredav.org
ATTENDEE;PARTSTAT=ACCEPTED:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user3.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->putPath('calendars/user2/cal/foo.ics', $oldObject);

        $this->deliver($oldObject, $newObject);
        $this->assertItemsInInbox('user2', 1);
        $this->assertItemsInInbox('user1', 0);

        $expected = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER;SCHEDULE-STATUS=1.2:mailto:user2.sabredav@sabredav.org
ATTENDEE;PARTSTAT=ACCEPTED:mailto:user2.sabredav@sabredav.org
ATTENDEE;PARTSTAT=ACCEPTED:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user3.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->assertVObjEquals(
            $expected,
            $newObject
        );

    }



    function testInviteUnknownUser(): void {

        $newObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user3.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->deliver(null, $newObject);

        $expected = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE;SCHEDULE-STATUS=3.7:mailto:user3.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->assertVObjEquals(
            $expected,
            $newObject
        );

    }

    function testInviteNoInboxUrl(): void {

        $newObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->server->on('propFind', function($propFind) {
            $propFind->set('{' . Plugin::NS_CALDAV . '}schedule-inbox-URL', null, 403);
        });
        $this->deliver(null, $newObject);

        $expected = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE;SCHEDULE-STATUS=5.2:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->assertVObjEquals(
            $expected,
            $newObject
        );

    }

    function testInviteNoCalendarHomeSet(): void {

        $newObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->server->on('propFind', function($propFind) {
            $propFind->set('{' . Plugin::NS_CALDAV . '}calendar-home-set', null, 403);
        });
        $this->deliver(null, $newObject);

        $expected = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE;SCHEDULE-STATUS=5.2:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->assertVObjEquals(
            $expected,
            $newObject
        );

    }
    function testInviteNoDefaultCalendar(): void {

        $newObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->server->on('propFind', function($propFind) {
            $propFind->set('{' . Plugin::NS_CALDAV . '}schedule-default-calendar-URL', null, 403);
        });
        $this->deliver(null, $newObject);

        $expected = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE;SCHEDULE-STATUS=5.2:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->assertVObjEquals(
            $expected,
            $newObject
        );

    }
    function testInviteNoScheduler(): void {

        $newObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->server->removeAllListeners('schedule');
        $this->deliver(null, $newObject);

        $expected = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE;SCHEDULE-STATUS=5.2:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->assertVObjEquals(
            $expected,
            $newObject
        );

    }
    function testInviteNoACLPlugin(): void {

        $this->setupACL = false;
        parent::setUp();

        $newObject = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->deliver(null, $newObject);

        $expected = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE;SCHEDULE-STATUS=5.2:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR
ICS;

        $this->assertVObjEquals(
            $expected,
            $newObject
        );

    }

    protected $calendarObjectUri;

    function deliver($oldObject, &$newObject, $disableScheduling = false): void {

        $this->server->httpRequest->setUrl($this->calendarObjectUri);
        if ($disableScheduling) {
            $this->server->httpRequest->setHeader('Schedule-Reply','F');
        }

        if ($oldObject && $newObject) {
            // update
            $this->putPath($this->calendarObjectUri, $oldObject);

            $stream = fopen('php://memory','r+');
            fwrite($stream, $newObject);
            rewind($stream);
            $modified = false;

            $this->server->emit('beforeWriteContent', [
                $this->calendarObjectUri,
                $this->server->tree->getNodeForPath($this->calendarObjectUri),
                &$stream,
                &$modified
            ]);
            if ($modified) {
                $newObject = $stream;
            }

        } elseif ($oldObject && !$newObject) {
            // delete
            $this->putPath($this->calendarObjectUri, $oldObject);

            $this->caldavSchedulePlugin->beforeUnbind(
                $this->calendarObjectUri
            );
        } else {

            // create
            $stream = fopen('php://memory','r+');
            fwrite($stream, $newObject);
            rewind($stream);
            $modified = false;
            $this->server->emit('beforeCreateFile', [
                $this->calendarObjectUri,
                &$stream,
                $this->server->tree->getNodeForPath(dirname($this->calendarObjectUri)),
                &$modified
            ]);

            if ($modified) {
                $newObject = $stream;
            }
        }

    }


    /**
     * Creates or updates a node at the specified path.
     *
     * This circumvents sabredav's internal server apis, so all events and
     * access control is skipped.
     *
     * @param string $path
     * @param string $data
     * @return void
     */
    function putPath($path, $data): void {

        list($parent, $base) = \Sabre\HTTP\UrlUtil::splitPath($path);
        $parentNode = $this->server->tree->getNodeForPath($parent);

        /*
        if ($parentNode->childExists($base)) {
            $childNode = $parentNode->getChild($base);
            $childNode->put($data);
        } else {*/
            $parentNode->createFile($base, $data);
        //}

    }

    function assertItemsInInbox($user, $count): void {

        $inboxNode = $this->server->tree->getNodeForPath('calendars/'.$user.'/inbox');
        $this->assertEquals($count, count($inboxNode->getChildren()));

    }

    function assertVObjEquals($expected, $actual): void {

        $format = function($data) {

            $data = trim($data, "\r\n");
            $data = str_replace("\r","", $data);
            // Unfolding lines.
            $data = str_replace("\n ", "", $data);

            return $data;

        };

        $this->assertEquals(
            $format($expected),
            $format($actual)
        );

    }

}

