<?php

namespace Sabre\DAV\Auth\Backend;

use Sabre\DAV;

abstract class AbstractPDOTest extends \PHPUnit_Framework_TestCase {

    abstract function getPDO();

    function testConstruct(): void {

        $pdo = $this->getPDO();
        $backend = new PDO($pdo);
        $this->assertTrue($backend instanceof PDO);

    }

    /**
     * @depends testConstruct
     */
    function testUserInfo(): void {

        $pdo = $this->getPDO();
        $backend = new PDO($pdo);

        $this->assertNull($backend->getDigestHash('realm','blabla'));

        $expected = 'hash';

        $this->assertEquals($expected, $backend->getDigestHash('realm','user'));

    }

}
