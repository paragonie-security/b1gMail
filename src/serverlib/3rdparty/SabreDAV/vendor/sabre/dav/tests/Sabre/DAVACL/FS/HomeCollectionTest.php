<?php

namespace Sabre\DAVACL\FS;

use Sabre\DAVACL\PrincipalBackend\Mock as PrincipalBackend;

class HomeCollectionTest extends \PHPUnit_Framework_TestCase {

    /**
     * System under test
     *
     * @var HomeCollection
     */
    protected $sut;

    protected $path;
    protected $name = 'thuis';

    function setUp(): void {

        $principalBackend = new PrincipalBackend();

        $this->path = SABRE_TEMPDIR . '/home';

        $this->sut = new HomeCollection($principalBackend, $this->path);
        $this->sut->collectionName = $this->name;


    }

    function tearDown(): void {

        \Sabre\TestUtil::clearTempDir();

    }

    function testGetName(): void {

        $this->assertEquals(
            $this->name,
            $this->sut->getName()
        );

    }

    function testGetChild(): void {

        $child = $this->sut->getChild('user1');
        $this->assertInstanceOf('Sabre\\DAVACL\\FS\\Collection', $child);
        $this->assertEquals('user1', $child->getName());

        $owner = 'principals/user1';
        $acl = [
            [
                'privilege' => '{DAV:}read',
                'principal' => $owner,
                'protected' => true,
            ],
            [
                'privilege' => '{DAV:}write',
                'principal' => $owner,
                'protected' => true,
            ],
        ];

        $this->assertEquals($acl, $child->getACL());
        $this->assertEquals($owner, $child->getOwner());

    }

    function testGetOwner(): void {

        $this->assertNull(
            $this->sut->getOwner()
        );

    }

    function testGetGroup(): void {

        $this->assertNull(
            $this->sut->getGroup()
        );

    }

    function testGetACL(): void {

        $acl = [
            [
                'principal' => '{DAV:}authenticated',
                'privilege' => '{DAV:}read',
                'protected' => true,
            ]
        ];

        $this->assertEquals(
            $acl,
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
