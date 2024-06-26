<?php

namespace Sabre\VObject\Component;

use Sabre\VObject;

class VCardTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider validateData
     */
    function testValidate($input, $expectedWarnings, $expectedRepairedOutput): void {

        $vcard = VObject\Reader::read($input);

        $warnings = $vcard->validate();

        $warnMsg = array();
        foreach($warnings as $warning) {
            $warnMsg[] = $warning['message'];
        }

        $this->assertEquals($expectedWarnings, $warnMsg);

        $vcard->validate(VObject\Component::REPAIR);

        $this->assertEquals(
            $expectedRepairedOutput,
            $vcard->serialize()
        );

    }

    public function validateData() {

        $tests = array();

        // Correct
        $tests[] = array(
            "BEGIN:VCARD\r\nVERSION:4.0\r\nFN:John Doe\r\nUID:foo\r\nEND:VCARD\r\n",
            array(),
            "BEGIN:VCARD\r\nVERSION:4.0\r\nFN:John Doe\r\nUID:foo\r\nEND:VCARD\r\n",
        );

        // No VERSION
        $tests[] = array(
            "BEGIN:VCARD\r\nFN:John Doe\r\nUID:foo\r\nEND:VCARD\r\n",
            array(
                'VERSION MUST appear exactly once in a VCARD component',
            ),
            "BEGIN:VCARD\r\nVERSION:3.0\r\nFN:John Doe\r\nUID:foo\r\nEND:VCARD\r\n",
        );

        // Unknown version
        $tests[] = array(
            "BEGIN:VCARD\r\nVERSION:2.2\r\nFN:John Doe\r\nUID:foo\r\nEND:VCARD\r\n",
            array(
                'Only vcard version 4.0 (RFC6350), version 3.0 (RFC2426) or version 2.1 (icm-vcard-2.1) are supported.',
            ),
            "BEGIN:VCARD\r\nVERSION:2.1\r\nFN:John Doe\r\nUID:foo\r\nEND:VCARD\r\n",
        );

        // No FN
        $tests[] = array(
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nEND:VCARD\r\n",
            array(
                'The FN property must appear in the VCARD component exactly 1 time',
            ),
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nEND:VCARD\r\n",
        );
        // No FN, N fallback
        $tests[] = array(
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nN:Doe;John;;;;;\r\nEND:VCARD\r\n",
            array(
                'The FN property must appear in the VCARD component exactly 1 time',
            ),
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nN:Doe;John;;;;;\r\nFN:John Doe\r\nEND:VCARD\r\n",
        );
        // No FN, N fallback, no first name
        $tests[] = array(
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nN:Doe;;;;;;\r\nEND:VCARD\r\n",
            array(
                'The FN property must appear in the VCARD component exactly 1 time',
            ),
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nN:Doe;;;;;;\r\nFN:Doe\r\nEND:VCARD\r\n",
        );

        // No FN, ORG fallback
        $tests[] = array(
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nORG:Acme Co.\r\nEND:VCARD\r\n",
            array(
                'The FN property must appear in the VCARD component exactly 1 time',
            ),
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nORG:Acme Co.\r\nFN:Acme Co.\r\nEND:VCARD\r\n",
        );
        return $tests;

    }

    function testGetDocumentType(): void {

        $vcard = new VCard(array(), false);
        $vcard->VERSION = '2.1';
        $this->assertEquals(VCard::VCARD21, $vcard->getDocumentType());

        $vcard = new VCard(array(), false);
        $vcard->VERSION = '3.0';
        $this->assertEquals(VCard::VCARD30, $vcard->getDocumentType());

        $vcard = new VCard(array(), false);
        $vcard->VERSION = '4.0';
        $this->assertEquals(VCard::VCARD40, $vcard->getDocumentType());

        $vcard = new VCard(array(), false);
        $this->assertEquals(VCard::UNKNOWN, $vcard->getDocumentType());
    }

    function testPreferredNoPref(): void {

        $vcard = <<<VCF
BEGIN:VCARD
VERSION:3.0
EMAIL:1@example.org
EMAIL:2@example.org
END:VCARD
VCF;

        $vcard = VObject\Reader::read($vcard);
        $this->assertEquals('1@example.org', $vcard->preferred('EMAIL')->getValue());

    }

    function testPreferredWithPref(): void {

        $vcard = <<<VCF
BEGIN:VCARD
VERSION:3.0
EMAIL:1@example.org
EMAIL;TYPE=PREF:2@example.org
END:VCARD
VCF;

        $vcard = VObject\Reader::read($vcard);
        $this->assertEquals('2@example.org', $vcard->preferred('EMAIL')->getValue());

    }

    function testPreferredWith40Pref(): void {

        $vcard = <<<VCF
BEGIN:VCARD
VERSION:4.0
EMAIL:1@example.org
EMAIL;PREF=3:2@example.org
EMAIL;PREF=2:3@example.org
END:VCARD
VCF;

        $vcard = VObject\Reader::read($vcard);
        $this->assertEquals('3@example.org', $vcard->preferred('EMAIL')->getValue());

    }

    function testPreferredNotFound(): void {

        $vcard = <<<VCF
BEGIN:VCARD
VERSION:4.0
END:VCARD
VCF;

        $vcard = VObject\Reader::read($vcard);
        $this->assertNull($vcard->preferred('EMAIL'));

    }

    function testNoUIDCardDAV(): void {

        $vcard = <<<VCF
BEGIN:VCARD
VERSION:4.0
FN:John Doe
END:VCARD
VCF;
        $this->assertValidate(
            $vcard,
            VCARD::PROFILE_CARDDAV,
            3,
            'vCards on CardDAV servers MUST have a UID property.'
        );

    }

    function testNoUIDNoCardDAV(): void {

        $vcard = <<<VCF
BEGIN:VCARD
VERSION:4.0
FN:John Doe
END:VCARD
VCF;
        $this->assertValidate(
            $vcard,
            0,
            2,
            'Adding a UID to a vCard property is recommended.'
        );

    }
    function testNoUIDNoCardDAVRepair(): void {

        $vcard = <<<VCF
BEGIN:VCARD
VERSION:4.0
FN:John Doe
END:VCARD
VCF;
        $this->assertValidate(
            $vcard,
            VCARD::REPAIR,
            1,
            'Adding a UID to a vCard property is recommended.'
        );

    }

    function testVCard21CardDAV(): void {

        $vcard = <<<VCF
BEGIN:VCARD
VERSION:2.1
FN:John Doe
UID:foo
END:VCARD
VCF;
        $this->assertValidate(
            $vcard,
            VCARD::PROFILE_CARDDAV,
            3,
            'CardDAV servers are not allowed to accept vCard 2.1.'
        );

    }

    function testVCard21NoCardDAV(): void {

        $vcard = <<<VCF
BEGIN:VCARD
VERSION:2.1
FN:John Doe
UID:foo
END:VCARD
VCF;
        $this->assertValidate(
            $vcard,
            0,
            0
        );

    }

    function assertValidate($vcf, $options, $expectedLevel, $expectedMessage = null): void {

        $vcal = VObject\Reader::read($vcf);
        $result = $vcal->validate($options);

        $this->assertValidateResult($result, $expectedLevel, $expectedMessage);

    }

    function assertValidateResult($input, $expectedLevel, $expectedMessage = null): void {

        $messages = array();
        foreach($input as $warning) {
            $messages[] = $warning['message'];
        }

        if ($expectedLevel === 0) {
            $this->assertEquals(0, count($input), 'No validation messages were expected. We got: ' . implode(', ', $messages));
        } else {
            $this->assertEquals(1, count($input), 'We expected exactly 1 validation message, We got: ' . implode(', ', $messages));

            $this->assertEquals($expectedMessage, $input[0]['message']);
            $this->assertEquals($expectedLevel, $input[0]['level']);
        }

    }
}
