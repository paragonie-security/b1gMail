<?php

namespace Sabre\VObject\Property\ICalendar;

use Sabre\VObject\Component;
use Sabre\VObject\Component\VCalendar;


class DateTimeTest extends \PHPUnit_Framework_TestCase {

    protected $vcal;

    function setUp(): void {

        $this->vcal = new VCalendar();

    }

    function testSetDateTime(): void {

        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setDateTime($dt);

        $this->assertEquals('19850704T013000', (string)$elem);
        $this->assertEquals('Europe/Amsterdam', (string)$elem['TZID']);
        $this->assertNull($elem['VALUE']);

        $this->assertTrue($elem->hasTime());

    }

    function testSetDateTimeLOCAL(): void {

        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setDateTime($dt, $isFloating = true);

        $this->assertEquals('19850704T013000', (string)$elem);
        $this->assertNull($elem['TZID']);

        $this->assertTrue($elem->hasTime());
    }

    function testSetDateTimeUTC(): void {

        $tz = new \DateTimeZone('GMT');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setDateTime($dt);

        $this->assertEquals('19850704T013000Z', (string)$elem);
        $this->assertNull($elem['TZID']);

        $this->assertTrue($elem->hasTime());
    }

    function testSetDateTimeLOCALTZ(): void {

        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setDateTime($dt);

        $this->assertEquals('19850704T013000', (string)$elem);
        $this->assertEquals('Europe/Amsterdam', (string)$elem['TZID']);

        $this->assertTrue($elem->hasTime());
    }

    function testSetDateTimeDATE(): void {

        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        $elem = $this->vcal->createProperty('DTSTART');
        $elem['VALUE'] = 'DATE';
        $elem->setDateTime($dt);

        $this->assertEquals('19850704', (string)$elem);
        $this->assertNull($elem['TZID']);
        $this->assertEquals('DATE', (string)$elem['VALUE']);

        $this->assertFalse($elem->hasTime());
    }

    function testSetValue(): void {

        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setValue($dt);

        $this->assertEquals('19850704T013000', (string)$elem);
        $this->assertEquals('Europe/Amsterdam', (string)$elem['TZID']);
        $this->assertNull($elem['VALUE']);

        $this->assertTrue($elem->hasTime());

    }

    function testSetValueArray(): void {

        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt1 = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt2 = new \DateTime('1985-07-04 02:30:00', $tz);
        $dt1->setTimeZone($tz);
        $dt2->setTimeZone($tz);

        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setValue(array($dt1, $dt2));

        $this->assertEquals('19850704T013000,19850704T023000', (string)$elem);
        $this->assertEquals('Europe/Amsterdam', (string)$elem['TZID']);
        $this->assertNull($elem['VALUE']);

        $this->assertTrue($elem->hasTime());

    }

    function testSetParts(): void {

        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt1 = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt2 = new \DateTime('1985-07-04 02:30:00', $tz);
        $dt1->setTimeZone($tz);
        $dt2->setTimeZone($tz);

        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setParts(array($dt1, $dt2));

        $this->assertEquals('19850704T013000,19850704T023000', (string)$elem);
        $this->assertEquals('Europe/Amsterdam', (string)$elem['TZID']);
        $this->assertNull($elem['VALUE']);

        $this->assertTrue($elem->hasTime());

    }
    function testSetPartsStrings(): void {

        $dt1 = '19850704T013000Z';
        $dt2 = '19850704T023000Z';

        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setParts(array($dt1, $dt2));

        $this->assertEquals('19850704T013000Z,19850704T023000Z', (string)$elem);
        $this->assertNull($elem['VALUE']);

        $this->assertTrue($elem->hasTime());

    }


    function testGetDateTimeCached(): void {

        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setDateTime($dt);

        $this->assertEquals($elem->getDateTime(), $dt);

    }

    function testGetDateTimeDateNULL(): void {

        $elem = $this->vcal->createProperty('DTSTART');
        $dt = $elem->getDateTime();

        $this->assertNull($dt);

    }

    function testGetDateTimeDateDATE(): void {

        $elem = $this->vcal->createProperty('DTSTART','19850704');
        $dt = $elem->getDateTime();

        $this->assertInstanceOf('DateTime', $dt);
        $this->assertEquals('1985-07-04 00:00:00', $dt->format('Y-m-d H:i:s'));

    }

    function testGetDateTimeDateDATEReferenceTimeZone(): void {

        $elem = $this->vcal->createProperty('DTSTART','19850704');

        $tz = new \DateTimeZone('America/Toronto');
        $dt = $elem->getDateTime($tz);
        $dt->setTimeZone(new \DateTimeZone('UTC'));

        $this->assertInstanceOf('DateTime', $dt);
        $this->assertEquals('1985-07-04 04:00:00', $dt->format('Y-m-d H:i:s'));

    }

    function testGetDateTimeDateFloating(): void {

        $elem = $this->vcal->createProperty('DTSTART','19850704T013000');
        $dt = $elem->getDateTime();

        $this->assertInstanceOf('DateTime', $dt);
        $this->assertEquals('1985-07-04 01:30:00', $dt->format('Y-m-d H:i:s'));

    }

    function testGetDateTimeDateFloatingReferenceTimeZone(): void {

        $elem = $this->vcal->createProperty('DTSTART','19850704T013000');

        $tz = new \DateTimeZone('America/Toronto');
        $dt = $elem->getDateTime($tz);
        $dt->setTimeZone(new \DateTimeZone('UTC'));

        $this->assertInstanceOf('DateTime', $dt);
        $this->assertEquals('1985-07-04 05:30:00', $dt->format('Y-m-d H:i:s'));

    }

    function testGetDateTimeDateUTC(): void {

        $elem = $this->vcal->createProperty('DTSTART','19850704T013000Z');
        $dt = $elem->getDateTime();

        $this->assertInstanceOf('DateTime', $dt);
        $this->assertEquals('1985-07-04 01:30:00', $dt->format('Y-m-d H:i:s'));
        $this->assertEquals('UTC', $dt->getTimeZone()->getName());

    }

    function testGetDateTimeDateLOCALTZ(): void {

        $elem = $this->vcal->createProperty('DTSTART','19850704T013000');
        $elem['TZID'] = 'Europe/Amsterdam';

        $dt = $elem->getDateTime();

        $this->assertInstanceOf('DateTime', $dt);
        $this->assertEquals('1985-07-04 01:30:00', $dt->format('Y-m-d H:i:s'));
        $this->assertEquals('Europe/Amsterdam', $dt->getTimeZone()->getName());

    }

    /**
     * @expectedException LogicException
     */
    function testGetDateTimeDateInvalid(): void {

        $elem = $this->vcal->createProperty('DTSTART','bla');
        $dt = $elem->getDateTime();

    }

    function testGetDateTimeWeirdTZ(): void {

        $elem = $this->vcal->createProperty('DTSTART','19850704T013000');
        $elem['TZID'] = '/freeassociation.sourceforge.net/Tzfile/Europe/Amsterdam';


        $event = $this->vcal->createComponent('VEVENT');
        $event->add($elem);

        $timezone = $this->vcal->createComponent('VTIMEZONE');
        $timezone->TZID = '/freeassociation.sourceforge.net/Tzfile/Europe/Amsterdam';
        $timezone->{'X-LIC-LOCATION'} = 'Europe/Amsterdam';

        $this->vcal->add($event);
        $this->vcal->add($timezone);

        $dt = $elem->getDateTime();

        $this->assertInstanceOf('DateTime', $dt);
        $this->assertEquals('1985-07-04 01:30:00', $dt->format('Y-m-d H:i:s'));
        $this->assertEquals('Europe/Amsterdam', $dt->getTimeZone()->getName());

    }

    function testGetDateTimeBadTimeZone(): void {

        $default = date_default_timezone_get();
        date_default_timezone_set('Canada/Eastern');

        $elem = $this->vcal->createProperty('DTSTART','19850704T013000');
        $elem['TZID'] = 'Moon';


        $event = $this->vcal->createComponent('VEVENT');
        $event->add($elem);

        $timezone = $this->vcal->createComponent('VTIMEZONE');
        $timezone->TZID = 'Moon';
        $timezone->{'X-LIC-LOCATION'} = 'Moon';


        $this->vcal->add($event);
        $this->vcal->add($timezone);

        $dt = $elem->getDateTime();

        $this->assertInstanceOf('DateTime', $dt);
        $this->assertEquals('1985-07-04 01:30:00', $dt->format('Y-m-d H:i:s'));
        $this->assertEquals('Canada/Eastern', $dt->getTimeZone()->getName());
        date_default_timezone_set($default);

    }

    function testUpdateValueParameter(): void {

        $dtStart = $this->vcal->createProperty('DTSTART', new \DateTime('2013-06-07 15:05:00'));
        $dtStart['VALUE'] = 'DATE';

        $this->assertEquals("DTSTART;VALUE=DATE:20130607\r\n", $dtStart->serialize());

    }

    function testValidate(): void {

        $exDate = $this->vcal->createProperty('EXDATE', '-00011130T143000Z');
        $messages = $exDate->validate();
        $this->assertEquals(1, count($messages));
        $this->assertEquals(3, $messages[0]['level']);

    }

    /**
     * This issue was discovered on the sabredav mailing list.
     */
    function testCreateDatePropertyThroughAdd(): void {

        $vcal = new VCalendar();
        $vevent = $vcal->add('VEVENT');

        $dtstart = $vevent->add(
            'DTSTART',
            new \DateTime('2014-03-07'),
            array('VALUE' => 'DATE')
        );

        $this->assertEquals("DTSTART;VALUE=DATE:20140307\r\n", $dtstart->serialize());

    }

}
