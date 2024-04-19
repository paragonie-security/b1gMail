<?php

namespace Sabre\VObject\Property;

use Sabre\VObject\Component\VCard;

class CompoundTest extends \PHPUnit_Framework_TestCase {

    function testSetParts(): void {

        $arr = array(
            'ABC, Inc.',
            'North American Division',
            'Marketing;Sales',
        );

        $vcard = new VCard();
        $elem = $vcard->createProperty('ORG');
        $elem->setParts($arr);

        $this->assertEquals('ABC\, Inc.;North American Division;Marketing\;Sales', $elem->getValue());
        $this->assertEquals(3, count($elem->getParts()));
        $parts = $elem->getParts();
        $this->assertEquals('Marketing;Sales', $parts[2]);

    }

    function testGetParts(): void {

        $str = 'ABC\, Inc.;North American Division;Marketing\;Sales';

        $vcard = new VCard();
        $elem = $vcard->createProperty('ORG');
        $elem->setRawMimeDirValue($str);

        $this->assertEquals(3, count($elem->getParts()));
        $parts = $elem->getParts();
        $this->assertEquals('Marketing;Sales', $parts[2]);
    }

    function testGetPartsNull(): void {

        $vcard = new VCard();
        $elem = $vcard->createProperty('ORG', null);

        $this->assertEquals(0, count($elem->getParts()));

    }

}
