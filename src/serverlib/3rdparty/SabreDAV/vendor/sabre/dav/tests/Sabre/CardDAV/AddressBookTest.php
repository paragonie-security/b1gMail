<?php

namespace Sabre\CardDAV;

use Sabre\DAV\PropPatch;

require_once 'Sabre/CardDAV/Backend/Mock.php';

class AddressBookTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Sabre\CardDAV\AddressBook
     */
    protected $ab;
    protected $backend;

    function setUp(): void {

        $this->backend = new Backend\Mock();
        $this->ab = new AddressBook(
            $this->backend,
            array(
                'uri' => 'book1',
                'id' => 'foo',
                '{DAV:}displayname' => 'd-name',
                'principaluri' => 'principals/user1',
            )
        );

    }

    function testGetName(): void {

        $this->assertEquals('book1', $this->ab->getName());

    }

    function testGetChild(): void {

        $card = $this->ab->getChild('card1');
        $this->assertInstanceOf('Sabre\\CardDAV\\Card', $card);
        $this->assertEquals('card1', $card->getName());

    }

    /**
     * @expectedException Sabre\DAV\Exception\NotFound
     */
    function testGetChildNotFound(): void {

        $card = $this->ab->getChild('card3');

    }

    function testGetChildren(): void {

        $cards = $this->ab->getChildren();
        $this->assertEquals(2, count($cards));

        $this->assertEquals('card1', $cards[0]->getName());
        $this->assertEquals('card2', $cards[1]->getName());

    }

    /**
     * @expectedException Sabre\DAV\Exception\MethodNotAllowed
     */
    function testCreateDirectory(): void {

        $this->ab->createDirectory('name');

    }

    function testCreateFile(): void {

        $file = fopen('php://memory','r+');
        fwrite($file,'foo');
        rewind($file);
        $this->ab->createFile('card2',$file);

        $this->assertEquals('foo', $this->backend->cards['foo']['card2']);

    }

    function testDelete(): void {

        $this->ab->delete();
        $this->assertEquals(array(), $this->backend->addressBooks);

    }

    /**
     * @expectedException Sabre\DAV\Exception\MethodNotAllowed
     */
    function testSetName(): void {

        $this->ab->setName('foo');

    }

    function testGetLastModified(): void {

        $this->assertNull($this->ab->getLastModified());

    }

    function testUpdateProperties(): void {

        $propPatch = new PropPatch([
            '{DAV:}displayname' => 'barrr',
        ]);
        $this->ab->propPatch($propPatch);
        $this->assertTrue($propPatch->commit());

        $this->assertEquals('barrr', $this->backend->addressBooks[0]['{DAV:}displayname']);

    }

    function testGetProperties(): void {

        $props = $this->ab->getProperties(array('{DAV:}displayname'));
        $this->assertEquals(array(
            '{DAV:}displayname' => 'd-name',
        ), $props);

    }

    function testACLMethods(): void {

        $this->assertEquals('principals/user1', $this->ab->getOwner());
        $this->assertNull($this->ab->getGroup());
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
        ), $this->ab->getACL());

    }

    /**
     * @expectedException Sabre\DAV\Exception\MethodNotAllowed
     */
    function testSetACL(): void {

       $this->ab->setACL(array());

    }

    function testGetSupportedPrivilegeSet(): void {

        $this->assertNull(
            $this->ab->getSupportedPrivilegeSet()
        );

    }

    function testGetSyncTokenNoSyncSupport(): void {

        $this->assertNull(null, $this->ab->getSyncToken());

    }
    function testGetChangesNoSyncSupport(): void {

        $this->assertNull($this->ab->getChanges(1,null));

    }

    function testGetSyncToken(): void {

        if (!SABRE_HASSQLITE) {
            $this->markTestSkipped('Sqlite is required for this test to run');
        }
        $ab = new AddressBook(TestUtil::getBackend(), [ 'id' => 1, '{DAV:}sync-token' => 2]);
        $this->assertEquals(2, $ab->getSyncToken());
    }

    function testGetSyncToken2(): void {

        if (!SABRE_HASSQLITE) {
            $this->markTestSkipped('Sqlite is required for this test to run');
        }
        $ab = new AddressBook(TestUtil::getBackend(), [ 'id' => 1, '{http://sabredav.org/ns}sync-token' => 2]);
        $this->assertEquals(2, $ab->getSyncToken());
    }

    function testGetChanges(): void {

        if (!SABRE_HASSQLITE) {
            $this->markTestSkipped('Sqlite is required for this test to run');
        }
        $ab = new AddressBook(TestUtil::getBackend(), [ 'id' => 1, '{DAV:}sync-token' => 2]);
        $this->assertEquals([
            'syncToken' => 2,
            'modified' => [],
            'deleted' => [],
            'added' => ['UUID-2345'],
        ], $ab->getChanges(1, 1));

    }


}
