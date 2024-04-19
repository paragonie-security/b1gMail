<?php

namespace Sabre\CardDAV;

use Sabre\DAV\MkCol;

class AddressBookHomeTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Sabre\CardDAV\AddressBookHome
     */
    protected $s;
    protected $backend;

    function setUp(): void {

        $this->backend = new Backend\Mock();
        $this->s = new AddressBookHome(
            $this->backend,
            'principals/user1'
        );

    }

    function testGetName(): void {

        $this->assertEquals('user1', $this->s->getName());

    }

    /**
     * @expectedException Sabre\DAV\Exception\MethodNotAllowed
     */
    function testSetName(): void {

        $this->s->setName('user2');

    }

    /**
     * @expectedException Sabre\DAV\Exception\MethodNotAllowed
     */
    function testDelete(): void {

        $this->s->delete();

    }

    function testGetLastModified(): void {

        $this->assertNull($this->s->getLastModified());

    }

    /**
     * @expectedException Sabre\DAV\Exception\MethodNotAllowed
     */
    function testCreateFile(): void {

        $this->s->createFile('bla');

    }

    /**
     * @expectedException Sabre\DAV\Exception\MethodNotAllowed
     */
    function testCreateDirectory(): void {

        $this->s->createDirectory('bla');

    }

    function testGetChild(): void {

        $child = $this->s->getChild('book1');
        $this->assertInstanceOf('Sabre\\CardDAV\\AddressBook', $child);
        $this->assertEquals('book1', $child->getName());

    }

    /**
     * @expectedException Sabre\DAV\Exception\NotFound
     */
    function testGetChild404(): void {

        $this->s->getChild('book2');

    }

    function testGetChildren(): void {

        $children = $this->s->getChildren();
        $this->assertEquals(1, count($children));
        $this->assertInstanceOf('Sabre\\CardDAV\\AddressBook', $children[0]);
        $this->assertEquals('book1', $children[0]->getName());

    }

    function testCreateExtendedCollection(): void {

        $resourceType = [ 
            '{' . Plugin::NS_CARDDAV . '}addressbook',
            '{DAV:}collection',
        ];
        $this->s->createExtendedCollection('book2', new MkCol($resourceType, ['{DAV:}displayname' => 'a-book 2']));

        $this->assertEquals(array(
            'id' => 'book2',
            'uri' => 'book2',
            '{DAV:}displayname' => 'a-book 2',
            'principaluri' => 'principals/user1',
        ), $this->backend->addressBooks[1]);

    }

    /**
     * @expectedException Sabre\DAV\Exception\InvalidResourceType
     */
    function testCreateExtendedCollectionInvalid(): void {

        $resourceType = array(
            '{DAV:}collection',
        );
        $this->s->createExtendedCollection('book2', new MkCol($resourceType, array('{DAV:}displayname' => 'a-book 2')));

    }


    function testACLMethods(): void {

        $this->assertEquals('principals/user1', $this->s->getOwner());
        $this->assertNull($this->s->getGroup());
        $this->assertEquals(array(
            array(
                'privilege' => '{DAV:}read',
                'principal' => 'principals/user1',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}write',
                'principal' => 'principals/user1',
                'protected' => true,
            ),
        ), $this->s->getACL());

    }

    /**
     * @expectedException Sabre\DAV\Exception\MethodNotAllowed
     */
    function testSetACL(): void {

       $this->s->setACL(array());

    }

    function testGetSupportedPrivilegeSet(): void {

        $this->assertNull(
            $this->s->getSupportedPrivilegeSet()
        );

    }
}
