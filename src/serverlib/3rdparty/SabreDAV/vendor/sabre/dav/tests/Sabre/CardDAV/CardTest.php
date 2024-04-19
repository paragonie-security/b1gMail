<?php

namespace Sabre\CardDAV;

class CardTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Sabre\CardDAV\Card
     */
    protected $card;
    /**
     * @var Sabre\CardDAV\MockBackend
     */
    protected $backend;

    function setUp(): void {

        $this->backend = new Backend\Mock();
        $this->card = new Card(
            $this->backend,
            array(
                'uri' => 'book1',
                'id' => 'foo',
                'principaluri' => 'principals/user1',
            ),
            array(
                'uri' => 'card1',
                'addressbookid' => 'foo',
                'carddata' => 'card',
            )
        );

    }

    function testGet(): void {

        $result = $this->card->get();
        $this->assertEquals('card', $result);

    }
    function testGet2(): void {

        $this->card = new Card(
            $this->backend,
            array(
                'uri' => 'book1',
                'id' => 'foo',
                'principaluri' => 'principals/user1',
            ),
            array(
                'uri' => 'card1',
                'addressbookid' => 'foo',
            )
        );
        $result = $this->card->get();
        $this->assertEquals("BEGIN:VCARD\nVERSION:3.0\nUID:12345\nEND:VCARD", $result);

    }


    /**
     * @depends testGet
     */
    function testPut(): void {

        $file = fopen('php://memory','r+');
        fwrite($file, 'newdata');
        rewind($file);
        $this->card->put($file);
        $result = $this->card->get();
        $this->assertEquals('newdata', $result);

    }


    function testDelete(): void {

        $this->card->delete();
        $this->assertEquals(1, count($this->backend->cards['foo']));

    }

    function testGetContentType(): void {

        $this->assertEquals('text/vcard; charset=utf-8', $this->card->getContentType());

    }

    function testGetETag(): void {

        $this->assertEquals('"' . md5('card') . '"' , $this->card->getETag());

    }

    function testGetETag2(): void {

        $card = new Card(
            $this->backend,
            array(
                'uri' => 'book1',
                'id' => 'foo',
                'principaluri' => 'principals/user1',
            ),
            array(
                'uri' => 'card1',
                'addressbookid' => 'foo',
                'carddata' => 'card',
                'etag' => '"blabla"',
            )
        );
        $this->assertEquals('"blabla"' , $card->getETag());

    }

    function testGetLastModified(): void {

        $this->assertEquals(null, $this->card->getLastModified());

    }

    function testGetSize(): void {

        $this->assertEquals(4, $this->card->getSize());
        $this->assertEquals(4, $this->card->getSize());

    }

    function testGetSize2(): void {

        $card = new Card(
            $this->backend,
            array(
                'uri' => 'book1',
                'id' => 'foo',
                'principaluri' => 'principals/user1',
            ),
            array(
                'uri' => 'card1',
                'addressbookid' => 'foo',
                'etag' => '"blabla"',
                'size' => 4,
            )
        );
        $this->assertEquals(4, $card->getSize());

    }

    function testACLMethods(): void {

        $this->assertEquals('principals/user1', $this->card->getOwner());
        $this->assertNull($this->card->getGroup());
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
        ), $this->card->getACL());

    }
    function testOverrideACL(): void {

        $card = new Card(
            $this->backend,
            array(
                'uri' => 'book1',
                'id' => 'foo',
                'principaluri' => 'principals/user1',
            ),
            array(
                'uri' => 'card1',
                'addressbookid' => 'foo',
                'carddata' => 'card',
                'acl' => array(
                    array(
                        'privilege' => '{DAV:}read',
                        'principal' => 'principals/user1',
                        'protected' => true,
                    ),
                ),
            )
        );
        $this->assertEquals(array(
            array(
                'privilege' => '{DAV:}read',
                'principal' => 'principals/user1',
                'protected' => true,
            ),
        ), $card->getACL());

    }

    /**
     * @expectedException Sabre\DAV\Exception\MethodNotAllowed
     */
    function testSetACL(): void {

       $this->card->setACL(array());

    }

    function testGetSupportedPrivilegeSet(): void {

        $this->assertNull(
            $this->card->getSupportedPrivilegeSet()
        );

    }

}
