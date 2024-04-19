<?php

namespace Sabre\VObject\Splitter;

use Sabre\VObject;

class VCardTest extends \PHPUnit_Framework_TestCase {

    function createStream($data) {

        $stream = fopen('php://memory','r+');
        fwrite($stream, $data);
        rewind($stream);
        return $stream;

    }

    function testVCardImportValidVCard(): void {
        $data = <<<EOT
BEGIN:VCARD
UID:foo
END:VCARD
EOT;
        $tempFile = $this->createStream($data);

        $objects = new VCard($tempFile);

        $count = 0;
        while($objects->getNext()) {
            $count++;
        }
        $this->assertEquals(1, $count);

    }

    /**
     * @expectedException Sabre\VObject\ParseException
     */
    function testVCardImportWrongType(): void {
        $event[] = <<<EOT
BEGIN:VEVENT
UID:foo1
DTSTAMP:20140122T233226Z
DTSTART:20140101T050000Z
END:VEVENT
EOT;

$event[] = <<<EOT
BEGIN:VEVENT
UID:foo2
DTSTAMP:20140122T233226Z
DTSTART:20140101T060000Z
END:VEVENT
EOT;

        $data = <<<EOT
BEGIN:VCALENDAR
$event[0]
$event[1]
END:VCALENDAR

EOT;
        $tempFile = $this->createStream($data);

        $splitter = new VCard($tempFile);

        while($object=$splitter->getNext()) {
        }

    }

    function testVCardImportValidVCardsWithCategories(): void {
        $data = <<<EOT
BEGIN:VCARD
UID:card-in-foo1-and-foo2
CATEGORIES:foo1,foo2
END:VCARD
BEGIN:VCARD
UID:card-in-foo1
CATEGORIES:foo1
END:VCARD
BEGIN:VCARD
UID:card-in-foo3
CATEGORIES:foo3
END:VCARD
BEGIN:VCARD
UID:card-in-foo1-and-foo3
CATEGORIES:foo1\,foo3
END:VCARD
EOT;
        $tempFile = $this->createStream($data);

        $splitter = new VCard($tempFile);

        $count = 0;
        while($object=$splitter->getNext()) {
            $count++;
        }
        $this->assertEquals(4, $count);

    }

    function testVCardImportEndOfData(): void {
        $data = <<<EOT
BEGIN:VCARD
UID:foo
END:VCARD
EOT;
        $tempFile = $this->createStream($data);

        $objects = new VCard($tempFile);
        $object=$objects->getNext();

        $this->assertNull($objects->getNext());


    }

    /**
     * @expectedException \Sabre\VObject\ParseException
     */
    function testVCardImportCheckInvalidArgumentException(): void {
        $data = <<<EOT
BEGIN:FOO
END:FOO
EOT;
        $tempFile = $this->createStream($data);

        $objects = new VCard($tempFile);
        while($objects->getNext()) { }

    }

    function testVCardImportMultipleValidVCards(): void {
        $data = <<<EOT
BEGIN:VCARD
UID:foo
END:VCARD
BEGIN:VCARD
UID:foo
END:VCARD
EOT;
        $tempFile = $this->createStream($data);

        $objects = new VCard($tempFile);

        $count = 0;
        while($objects->getNext()) {
            $count++;
        }
        $this->assertEquals(2, $count);

    }

    function testImportMultipleSeparatedWithNewLines(): void {
        $data = <<<EOT
BEGIN:VCARD
UID:foo
END:VCARD


BEGIN:VCARD
UID:foo
END:VCARD


EOT;
        $tempFile = $this->createStream($data);
        $objects = new VCard($tempFile);

        $count = 0;
        while ($objects->getNext()) {
            $count++;
        }
        $this->assertEquals(2, $count);
    }

    function testVCardImportVCardWithoutUID(): void {
        $data = <<<EOT
BEGIN:VCARD
END:VCARD
EOT;
        $tempFile = $this->createStream($data);

        $objects = new VCard($tempFile);

        $count = 0;
        while($objects->getNext()) {
            $count++;
        }

        $this->assertEquals(1, $count);
    }

}
