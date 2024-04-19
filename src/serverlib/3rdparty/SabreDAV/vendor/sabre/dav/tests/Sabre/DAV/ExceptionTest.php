<?php

namespace Sabre\DAV;

class ExceptionTest extends \PHPUnit_Framework_TestCase {

    function testStatus(): void {

        $e = new Exception();
        $this->assertEquals(500,$e->getHTTPCode());

    }

    function testExceptionStatuses(): void {

        $c = array(
            'Sabre\\DAV\\Exception\\NotAuthenticated'    => 401,
            'Sabre\\DAV\\Exception\\InsufficientStorage' => 507,
        );

        foreach($c as $class=>$status) {

            $obj = new $class();
            $this->assertEquals($status, $obj->getHTTPCode());

        }

    }

}
