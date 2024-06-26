<?php

namespace Sabre\VObject;

class ElementListTest extends \PHPUnit_Framework_TestCase {

    function testIterate(): void {

        $cal = new Component\VCalendar();
        $sub = $cal->createComponent('VEVENT');

        $elems = array(
            $sub,
            clone $sub,
            clone $sub
        );

        $elemList = new ElementList($elems);

        $count = 0;
        foreach($elemList as $key=>$subcomponent) {

           $count++;
           $this->assertInstanceOf('Sabre\\VObject\\Component',$subcomponent);

        }
        $this->assertEquals(3,$count);
        $this->assertEquals(2,$key);

    }


}
