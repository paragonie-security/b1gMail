<?php

namespace Sabre\VObject;

class ParameterTest extends \PHPUnit_Framework_TestCase {

    function testSetup(): void {

        $cal = new Component\VCalendar();

        $param = new Parameter($cal, 'name','value');
        $this->assertEquals('NAME',$param->name);
        $this->assertEquals('value',$param->getValue());

    }

    function testSetupNameLess(): void {

        $card = new Component\VCard();

        $param = new Parameter($card, null,'URL');
        $this->assertEquals('VALUE',$param->name);
        $this->assertEquals('URL',$param->getValue());
        $this->assertTrue($param->noName);

    }

    function testModify(): void {

        $cal = new Component\VCalendar();

        $param = new Parameter($cal, 'name', null);
        $param->addValue(1);
        $this->assertEquals(array(1), $param->getParts());

        $param->setParts(array(1,2));
        $this->assertEquals(array(1,2), $param->getParts());

        $param->addValue(3);
        $this->assertEquals(array(1,2,3), $param->getParts());

        $param->setValue(4);
        $param->addValue(5);
        $this->assertEquals(array(4,5), $param->getParts());

    }

    function testCastToString(): void {

        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name', 'value');
        $this->assertEquals('value',$param->__toString());
        $this->assertEquals('value',(string)$param);

    }

    function testCastNullToString(): void {

        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name', null);
        $this->assertEquals('',$param->__toString());
        $this->assertEquals('',(string)$param);

    }

    function testSerialize(): void {

        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name', 'value');
        $this->assertEquals('NAME=value',$param->serialize());

    }

    function testSerializeEmpty(): void {

        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name', null);
        $this->assertEquals('NAME=',$param->serialize());

    }

    function testSerializeComplex(): void {

        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name',array("val1", "val2;", "val3^", "val4\n", "val5\""));
        $this->assertEquals('NAME=val1,"val2;","val3^^","val4^n","val5^\'"',$param->serialize());

    }

    /**
     * iCal 7.0 (OSX 10.9) has major issues with the EMAIL property, when the
     * value contains a plus sign, and it's not quoted.
     *
     * So we specifically added support for that.
     */
    function testSerializePlusSign(): void {

        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'EMAIL',"user+something@example.org");
        $this->assertEquals('EMAIL="user+something@example.org"',$param->serialize());

    }

    function testIterate(): void {

        $cal = new Component\VCalendar();

        $param = new Parameter($cal, 'name', array(1,2,3,4));
        $result = array();

        foreach($param as $value) {
            $result[] = $value;
        }

        $this->assertEquals(array(1,2,3,4), $result);

    }

    function testSerializeColon(): void {

        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name','va:lue');
        $this->assertEquals('NAME="va:lue"',$param->serialize());

    }

    function testSerializeSemiColon(): void {

        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name','va;lue');
        $this->assertEquals('NAME="va;lue"',$param->serialize());

    }

}
