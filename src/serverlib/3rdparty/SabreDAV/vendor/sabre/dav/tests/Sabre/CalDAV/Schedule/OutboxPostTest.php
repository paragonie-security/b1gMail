<?php

namespace Sabre\CalDAV\Schedule;

use Sabre\HTTP;
use Sabre\VObject;
use Sabre\DAV;

class OutboxPostTest extends \Sabre\DAVServerTest {

    protected $setupCalDAV = true;
    protected $setupACL = true;
    protected $autoLogin = 'user1';
    protected $setupCalDAVScheduling = true;

    function testPostPassThruNotFound(): void {

        $req = HTTP\Sapi::createFromServerArray(array(
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/notfound',
            'HTTP_CONTENT_TYPE' => 'text/calendar',
        ));

        $this->assertHTTPStatus(501, $req);

    }

    function testPostPassThruNotTextCalendar(): void {

        $req = HTTP\Sapi::createFromServerArray(array(
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/calendars/user1/outbox',
        ));

        $this->assertHTTPStatus(501, $req);

    }

    function testPostPassThruNoOutBox(): void {

        $req = HTTP\Sapi::createFromServerArray(array(
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/calendars',
            'HTTP_CONTENT_TYPE' => 'text/calendar',
        ));

        $this->assertHTTPStatus(501, $req);

    }

    function testInvalidIcalBody(): void {

        $req = HTTP\Sapi::createFromServerArray(array(
            'REQUEST_METHOD'  => 'POST',
            'REQUEST_URI'     => '/calendars/user1/outbox',
            'HTTP_ORIGINATOR' => 'mailto:user1.sabredav@sabredav.org',
            'HTTP_RECIPIENT'  => 'mailto:user2@example.org',
            'HTTP_CONTENT_TYPE' => 'text/calendar',
        ));
        $req->setBody('foo');

        $this->assertHTTPStatus(400, $req);

    }

    function testNoVEVENT(): void {

        $req = HTTP\Sapi::createFromServerArray(array(
            'REQUEST_METHOD'  => 'POST',
            'REQUEST_URI'     => '/calendars/user1/outbox',
            'HTTP_ORIGINATOR' => 'mailto:user1.sabredav@sabredav.org',
            'HTTP_RECIPIENT'  => 'mailto:user2@example.org',
            'HTTP_CONTENT_TYPE' => 'text/calendar',
        ));

        $body = array(
            'BEGIN:VCALENDAR',
            'BEGIN:VTIMEZONE',
            'END:VTIMEZONE',
            'END:VCALENDAR',
        );

        $req->setBody(implode("\r\n",$body));

        $this->assertHTTPStatus(400, $req);

    }

    function testNoMETHOD(): void {

        $req = HTTP\Sapi::createFromServerArray(array(
            'REQUEST_METHOD'  => 'POST',
            'REQUEST_URI'     => '/calendars/user1/outbox',
            'HTTP_ORIGINATOR' => 'mailto:user1.sabredav@sabredav.org',
            'HTTP_RECIPIENT'  => 'mailto:user2@example.org',
            'HTTP_CONTENT_TYPE' => 'text/calendar',
        ));

        $body = array(
            'BEGIN:VCALENDAR',
            'BEGIN:VEVENT',
            'END:VEVENT',
            'END:VCALENDAR',
        );

        $req->setBody(implode("\r\n",$body));

        $this->assertHTTPStatus(400, $req);

    }

    function testUnsupportedMethod(): void {

        $req = HTTP\Sapi::createFromServerArray(array(
            'REQUEST_METHOD'  => 'POST',
            'REQUEST_URI'     => '/calendars/user1/outbox',
            'HTTP_ORIGINATOR' => 'mailto:user1.sabredav@sabredav.org',
            'HTTP_RECIPIENT'  => 'mailto:user2@example.org',
            'HTTP_CONTENT_TYPE' => 'text/calendar',
        ));

        $body = array(
            'BEGIN:VCALENDAR',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'END:VEVENT',
            'END:VCALENDAR',
        );

        $req->setBody(implode("\r\n",$body));

        $this->assertHTTPStatus(501, $req);

    }

}
