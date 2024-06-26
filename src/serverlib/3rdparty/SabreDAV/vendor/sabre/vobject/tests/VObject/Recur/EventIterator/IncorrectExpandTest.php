<?php

namespace Sabre\VObject\Recur\EventIterator;

use
    DateTime,
    DateTimeZone,
    Sabre\VObject\Reader;

/**
 * This is a unittest for Issue #53.
 */
class RecurrenceIteratorIncorrectExpandTest extends \PHPUnit_Framework_TestCase {

    function testExpand(): void {

        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foo
DTSTART:20130711T050000Z
DTEND:20130711T053000Z
RRULE:FREQ=DAILY;INTERVAL=1;COUNT=2
END:VEVENT
BEGIN:VEVENT
UID:foo
DTSTART:20130719T050000Z
DTEND:20130719T053000Z
RECURRENCE-ID:20130712T050000Z
END:VEVENT
END:VCALENDAR
ICS;

        $vcal = Reader::read($input);
        $this->assertInstanceOf('Sabre\\VObject\\Component\\VCalendar', $vcal);

        $vcal->expand(new DateTime('2011-01-01'), new DateTime('2014-01-01'));

        $result = $vcal->serialize();

        $output = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foo
DTSTART:20130711T050000Z
DTEND:20130711T053000Z
RECURRENCE-ID:20130711T050000Z
END:VEVENT
BEGIN:VEVENT
UID:foo
DTSTART:20130719T050000Z
DTEND:20130719T053000Z
RECURRENCE-ID:20130712T050000Z
END:VEVENT
END:VCALENDAR

ICS;
        $this->assertEquals($output, str_replace("\r", "", $result));
    
    }

}
