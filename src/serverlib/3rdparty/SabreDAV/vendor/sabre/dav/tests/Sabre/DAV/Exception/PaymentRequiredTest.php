<?php

namespace Sabre\DAV\Exception;

class PaymentRequiredTest extends \PHPUnit_Framework_TestCase {

    function testGetHTTPCode(): void {

        $ex = new PaymentRequired();
        $this->assertEquals(402, $ex->getHTTPCode());

    }

}
