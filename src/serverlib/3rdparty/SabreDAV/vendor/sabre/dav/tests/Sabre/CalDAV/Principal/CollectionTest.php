<?php

namespace Sabre\CalDAV\Principal;
use Sabre\DAVACL;

class CollectionTest extends \PHPUnit_Framework_TestCase {

    function testGetChildForPrincipal(): void {

        $back = new DAVACL\PrincipalBackend\Mock();
        $col = new Collection($back);
        $r = $col->getChildForPrincipal(array(
            'uri' => 'principals/admin',
        ));
        $this->assertInstanceOf('Sabre\\CalDAV\\Principal\\User', $r);

    }

}
