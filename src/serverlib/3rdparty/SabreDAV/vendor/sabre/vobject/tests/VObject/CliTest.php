<?php

namespace Sabre\VObject;

/**
 * Tests the cli.
 *
 * Warning: these tests are very rudimentary.
 */
class CliTest extends \PHPUnit_Framework_TestCase {

    public function setUp(): void {

        $this->cli = new CliMock();
        $this->cli->stderr = fopen('php://memory','r+');
        $this->cli->stdout = fopen('php://memory','r+');

    }

    public function testInvalidArg(): void {

        $this->assertEquals(
            1,
            $this->cli->main(array('vobject', '--hi'))
        );
        rewind($this->cli->stderr);
        $this->assertTrue(strlen(stream_get_contents($this->cli->stderr)) > 100);

    }

    public function testQuiet(): void {

        $this->assertEquals(
            1,
            $this->cli->main(array('vobject', '-q'))
        );
        $this->assertTrue($this->cli->quiet);

        rewind($this->cli->stderr);
        $this->assertEquals(0, strlen(stream_get_contents($this->cli->stderr)));

    }

    public function testHelp(): void {

        $this->assertEquals(
            0,
            $this->cli->main(array('vobject', '-h'))
        );
        rewind($this->cli->stderr);
        $this->assertTrue(strlen(stream_get_contents($this->cli->stderr)) > 100);

    }

    public function testFormat(): void {

        $this->assertEquals(
            1,
            $this->cli->main(array('vobject', '--format=jcard'))
        );

        rewind($this->cli->stderr);
        $this->assertTrue(strlen(stream_get_contents($this->cli->stderr)) > 100);

        $this->assertEquals('jcard', $this->cli->format);

    }

    public function testFormatInvalid(): void {

        $this->assertEquals(
            1,
            $this->cli->main(array('vobject', '--format=foo'))
        );

        rewind($this->cli->stderr);
        $this->assertTrue(strlen(stream_get_contents($this->cli->stderr)) > 100);

        $this->assertNull($this->cli->format);

    }

    public function testInputFormatInvalid(): void {

        $this->assertEquals(
            1,
            $this->cli->main(array('vobject', '--inputformat=foo'))
        );

        rewind($this->cli->stderr);
        $this->assertTrue(strlen(stream_get_contents($this->cli->stderr)) > 100);

        $this->assertNull($this->cli->format);

    }


    public function testNoInputFile(): void {

        $this->assertEquals(
            1,
            $this->cli->main(array('vobject', 'color'))
        );

        rewind($this->cli->stderr);
        $this->assertTrue(strlen(stream_get_contents($this->cli->stderr)) > 100);

    }

    public function testTooManyArgs(): void {

        $this->assertEquals(
            1,
            $this->cli->main(array('vobject', 'color', 'a', 'b', 'c'))
        );

    }

    public function testUnknownCommand(): void {

        $this->assertEquals(
            1,
            $this->cli->main(array('vobject', 'foo', '-'))
        );

    }

    public function testConvertJson(): void {

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<ICS
BEGIN:VCARD
VERSION:3.0
FN:Cowboy Henk
END:VCARD
ICS
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        $this->assertEquals(
            0,
            $this->cli->main(array('vobject', 'convert','--format=json', '-'))
        );

        rewind($this->cli->stdout);
        $version = Version::VERSION;
        $this->assertEquals(
            '["vcard",[["version",{},"text","4.0"],["prodid",{},"text","-\/\/Sabre\/\/Sabre VObject '. $version .'\/\/EN"],["fn",{},"text","Cowboy Henk"]]]',
            stream_get_contents($this->cli->stdout)
        );

    }

    public function testConvertJCardPretty(): void {

        if (version_compare(PHP_VERSION, '5.4.0') < 0) {
            $this->markTestSkipped('This test required PHP 5.4.0');
        }

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<ICS
BEGIN:VCARD
VERSION:3.0
FN:Cowboy Henk
END:VCARD
ICS
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        $this->assertEquals(
            0,
            $this->cli->main(array('vobject', 'convert','--format=jcard', '--pretty', '-'))
        );

        rewind($this->cli->stdout);
        $version = Version::VERSION;

        // PHP 5.5.12 changed the output

        $expected = <<<JCARD
[
    "vcard",
    [
        [
            "versi
JCARD;

          $this->assertStringStartsWith(
            $expected,
            stream_get_contents($this->cli->stdout)
        );

    }

    public function testConvertJCalFail(): void {

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<ICS
BEGIN:VCARD
VERSION:3.0
FN:Cowboy Henk
END:VCARD
ICS
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        $this->assertEquals(
            2,
            $this->cli->main(array('vobject', 'convert','--format=jcal', '--inputformat=mimedir', '-'))
        );

    }

    public function testConvertMimeDir(): void {

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<JCARD
[
    "vcard",
    [
        [
            "version",
            {

            },
            "text",
            "4.0"
        ],
        [
            "prodid",
            {

            },
            "text",
            "-\/\/Sabre\/\/Sabre VObject 3.1.0\/\/EN"
        ],
        [
            "fn",
            {

            },
            "text",
            "Cowboy Henk"
        ]
    ]
]
JCARD
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        $this->assertEquals(
            0,
            $this->cli->main(array('vobject', 'convert','--format=mimedir', '--inputformat=json', '--pretty', '-'))
        );

        rewind($this->cli->stdout);
        $expected = <<<VCF
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject 3.1.0//EN
FN:Cowboy Henk
END:VCARD

VCF;

          $this->assertEquals(
            strtr($expected, array("\n"=>"\r\n")),
            stream_get_contents($this->cli->stdout)
        );

    }

    public function testConvertDefaultFormats(): void {

        $inputStream = fopen('php://memory','r+');
        $outputFile = SABRE_TEMPDIR . 'bar.json';

        $this->assertEquals(
            2,
            $this->cli->main(array('vobject', 'convert','foo.json',$outputFile))
        );

        $this->assertEquals('json', $this->cli->inputFormat);
        $this->assertEquals('json', $this->cli->format);

    }

    public function testConvertDefaultFormats2(): void {

        $outputFile = SABRE_TEMPDIR . 'bar.ics';

        $this->assertEquals(
            2,
            $this->cli->main(array('vobject', 'convert','foo.ics',$outputFile))
        );

        $this->assertEquals('mimedir', $this->cli->inputFormat);
        $this->assertEquals('mimedir', $this->cli->format);

    }

    public function testVCard3040(): void {

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<VCARD
BEGIN:VCARD
VERSION:3.0
PRODID:-//Sabre//Sabre VObject 3.1.0//EN
FN:Cowboy Henk
END:VCARD

VCARD
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        $this->assertEquals(
            0,
            $this->cli->main(array('vobject', 'convert','--format=vcard40', '--pretty', '-'))
        );

        rewind($this->cli->stdout);

        $version = Version::VERSION;
        $expected = <<<VCF
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject $version//EN
FN:Cowboy Henk
END:VCARD

VCF;

          $this->assertEquals(
            strtr($expected, array("\n"=>"\r\n")),
            stream_get_contents($this->cli->stdout)
        );

    }

    public function testVCard4030(): void {

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<VCARD
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject 3.1.0//EN
FN:Cowboy Henk
END:VCARD

VCARD
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        $this->assertEquals(
            0,
            $this->cli->main(array('vobject', 'convert','--format=vcard30', '--pretty', '-'))
        );

        $version = Version::VERSION;

        rewind($this->cli->stdout);
        $expected = <<<VCF
BEGIN:VCARD
VERSION:3.0
PRODID:-//Sabre//Sabre VObject $version//EN
FN:Cowboy Henk
END:VCARD

VCF;

          $this->assertEquals(
            strtr($expected, array("\n"=>"\r\n")),
            stream_get_contents($this->cli->stdout)
        );

    }

    public function testVCard4021(): void {

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<VCARD
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject 3.1.0//EN
FN:Cowboy Henk
END:VCARD

VCARD
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        // vCard 2.1 is not supported yet, so this returns a failure.
        $this->assertEquals(
            2,
            $this->cli->main(array('vobject', 'convert','--format=vcard21', '--pretty', '-'))
        );

    }

    function testValidate(): void {

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<VCARD
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject 3.1.0//EN
UID:foo
FN:Cowboy Henk
END:VCARD

VCARD
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;
        $result = $this->cli->main(array('vobject', 'validate', '-'));

        $this->assertEquals(
            0,
            $result
        );

    }

    function testValidateFail(): void {

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<VCARD
BEGIN:VCALENDAR
VERSION:2.0
END:VCARD

VCARD
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;
        // vCard 2.1 is not supported yet, so this returns a failure.
        $this->assertEquals(
            2,
            $this->cli->main(array('vobject', 'validate', '-'))
        );

    }

    function testValidateFail2(): void {

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<VCARD
BEGIN:VCALENDAR
VERSION:5.0
END:VCALENDAR

VCARD
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;
        // vCard 2.1 is not supported yet, so this returns a failure.
        $this->assertEquals(
            2,
            $this->cli->main(array('vobject', 'validate', '-'))
        );

    }

    function testRepair(): void {

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<VCARD
BEGIN:VCARD
VERSION:5.0
END:VCARD

VCARD
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;
        // vCard 2.1 is not supported yet, so this returns a failure.
        $this->assertEquals(
            2,
            $this->cli->main(array('vobject', 'repair', '-'))
        );

        rewind($this->cli->stdout);
        $this->assertRegExp("/^BEGIN:VCARD\r\nVERSION:2.1\r\nUID:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\r\nEND:VCARD\r\n$/", stream_get_contents($this->cli->stdout));
    }

    function testRepairNothing(): void {

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<VCARD
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject 3.1.0//EN
BEGIN:VEVENT
UID:foo
DTSTAMP:20140122T233226Z
DTSTART:20140101T120000Z
END:VEVENT
END:VCALENDAR

VCARD
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;
        // vCard 2.1 is not supported yet, so this returns a failure.

        $result = $this->cli->main(array('vobject', 'repair', '-'));

        rewind($this->cli->stderr);
        $error = stream_get_contents($this->cli->stderr);

        $this->assertEquals(
            0,
            $result,
            "This should have been error free. stderr output:\n" . $error
        );

    }

    /**
     * Note: this is a very shallow test, doesn't dig into the actual output,
     * but just makes sure there's no errors thrown.
     *
     * The colorizer is not a critical component, it's mostly a debugging tool.
     */
    function testColorCalendar(): void {

        $inputStream = fopen('php://memory','r+');

        $version = Version::VERSION;

        /**
         * This object is not valid, but it's designed to hit every part of the
         * colorizer source.
         */
        fwrite($inputStream, <<<VCARD
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject {$version}//EN
BEGIN:VTIMEZONE
END:VTIMEZONE
BEGIN:VEVENT
ATTENDEE;RSVP=TRUE:mailto:foo@example.org
REQUEST-STATUS:5;foo
ATTACH:blabla
END:VEVENT
END:VCALENDAR

VCARD
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;
        // vCard 2.1 is not supported yet, so this returns a failure.

        $result = $this->cli->main(array('vobject', 'color', '-'));

        rewind($this->cli->stderr);
        $error = stream_get_contents($this->cli->stderr);

        $this->assertEquals(
            0,
            $result,
            "This should have been error free. stderr output:\n" . $error
        );

    }

    /**
     * Note: this is a very shallow test, doesn't dig into the actual output,
     * but just makes sure there's no errors thrown.
     *
     * The colorizer is not a critical component, it's mostly a debugging tool.
     */
    function testColorVCard(): void {

        $inputStream = fopen('php://memory','r+');

        $version = Version::VERSION;

        /**
         * This object is not valid, but it's designed to hit every part of the
         * colorizer source.
         */
        fwrite($inputStream, <<<VCARD
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject {$version}//EN
ADR:1;2;3;4a,4b;5;6
group.TEL:123454768
END:VCARD

VCARD
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;
        // vCard 2.1 is not supported yet, so this returns a failure.

        $result = $this->cli->main(array('vobject', 'color', '-'));

        rewind($this->cli->stderr);
        $error = stream_get_contents($this->cli->stderr);

        $this->assertEquals(
            0,
            $result,
            "This should have been error free. stderr output:\n" . $error
        );

    }
}

class CliMock extends Cli {

    public $log = array();

    public $quiet = false;

    public $format;

    public $pretty;

    public $stdin;

    public $stdout;

    public $stderr;

    public $inputFormat;

    public $outputFormat;

}
