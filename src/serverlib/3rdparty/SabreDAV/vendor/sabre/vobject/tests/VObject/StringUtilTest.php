<?php

namespace Sabre\VObject;

class StringUtilTest extends \PHPUnit_Framework_TestCase {

    function testNonUTF8(): void {

        $string = StringUtil::isUTF8(chr(0xbf));

        $this->assertEquals(false, $string);

    }

    function testIsUTF8(): void {

        $string = StringUtil::isUTF8('I 💚 SabreDAV');

        $this->assertEquals(true, $string);

    }

    function testUTF8ControlChar(): void {

        $string = StringUtil::isUTF8(chr(0x00));

        $this->assertEquals(false, $string);

    }

    function testConvertToUTF8nonUTF8(): void {

        $string = StringUtil::convertToUTF8(chr(0xbf));

        $this->assertEquals(utf8_encode(chr(0xbf)), $string);

    }

    function testConvertToUTF8IsUTF8(): void {

        $string = StringUtil::convertToUTF8('I 💚 SabreDAV');

        $this->assertEquals('I 💚 SabreDAV', $string);

    }

    function testConvertToUTF8ControlChar(): void {

        $string = StringUtil::convertToUTF8(chr(0x00));

        $this->assertEquals('', $string);

    }

}
