<?php

namespace Sabre\DAVACL\FS;

class FileTest extends \PHPUnit_Framework_TestCase {

    /**
     * System under test
     *
     * @var File
     */
    protected $sut;

    protected $path = 'foo';
    protected $acl = [
        [
            'privilege' => '{DAV:}read',
            'principal' => '{DAV:}authenticated',
        ]
    ];

    protected $owner = 'principals/evert';

    function setUp(): void {

        $this->sut = new File($this->path, $this->acl, $this->owner);

    }

    function testGetOwner(): void {

        $this->assertEquals(
            $this->owner,
            $this->sut->getOwner()
        );

    }

    function testGetGroup(): void {

        $this->assertNull(
            $this->sut->getGroup()
        );

    }

    function testGetACL(): void {

        $this->assertEquals(
            $this->acl,
            $this->sut->getACL()
        );

    }

    /**
     * @expectedException \Sabre\DAV\Exception\Forbidden
     */
    function testSetAcl(): void {

        $this->sut->setACL([]);

    }

    function testGetSupportedPrivilegeSet(): void {

        $this->assertNull(
            $this->sut->getSupportedPrivilegeSet()
        );

    }

}
