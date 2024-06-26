<?php

namespace Sabre\DAV\Xml\Property;

use Sabre\DAV\Xml\XmlTest;
use DateTime;
use DateTimeZone;

class LastModifiedTest extends XmlTest {

    function testSerializeDateTime(): void {

        $dt = new DateTime('2015-03-24 11:47:00', new DateTimeZone('America/Vancouver'));
        $val = ['{DAV:}getlastmodified' => new GetLastModified($dt)];

        $result = $this->write($val);
        $expected = <<<XML
<?xml version="1.0"?>
<d:getlastmodified xmlns:d="DAV:">Tue, 24 Mar 2015 18:47:00 GMT</d:getlastmodified>
XML;

        $this->assertXmlStringEqualsXmlString($expected, $result);

    }

    function testSerializeTimeStamp(): void {

        $dt = new DateTime('2015-03-24 11:47:00', new DateTimeZone('America/Vancouver'));
        $dt = $dt->getTimeStamp();
        $val = ['{DAV:}getlastmodified' => new GetLastModified($dt)];

        $result = $this->write($val);
        $expected = <<<XML
<?xml version="1.0"?>
<d:getlastmodified xmlns:d="DAV:">Tue, 24 Mar 2015 18:47:00 GMT</d:getlastmodified>
XML;

        $this->assertXmlStringEqualsXmlString($expected, $result);

    }

    function testDeserialize(): void {

        $input = <<<XML
<?xml version="1.0"?>
<d:getlastmodified xmlns:d="DAV:">Tue, 24 Mar 2015 18:47:00 GMT</d:getlastmodified>
XML;

        $elementMap = ['{DAV:}getlastmodified' => 'Sabre\DAV\Xml\Property\GetLastModified'];
        $result = $this->parse($input, $elementMap);

        $this->assertEquals(
            new DateTime('2015-03-24 18:47:00', new DateTimeZone('UTC')),
            $result['value']->getTime()
        );

    }

}
