<?php

namespace Sabre\CalDAV\Principal;
use Sabre\DAVACL;

class UserTest extends \PHPUnit_Framework_TestCase {

    function getInstance() {

        $backend = new DAVACL\PrincipalBackend\Mock();
        $backend->addPrincipal(array(
            'uri' => 'principals/user/calendar-proxy-read',
        ));
        $backend->addPrincipal(array(
            'uri' => 'principals/user/calendar-proxy-write',
        ));
        $backend->addPrincipal(array(
            'uri' => 'principals/user/random',
        ));
        return new User($backend, array(
            'uri' => 'principals/user',
        ));

    }

    /**
     * @expectedException Sabre\DAV\Exception\Forbidden
     */
    function testCreateFile(): void {

        $u = $this->getInstance();
        $u->createFile('test');

    }

    /**
     * @expectedException Sabre\DAV\Exception\Forbidden
     */
    function testCreateDirectory(): void {

        $u = $this->getInstance();
        $u->createDirectory('test');

    }

    function testGetChildProxyRead(): void {

        $u = $this->getInstance();
        $child = $u->getChild('calendar-proxy-read');
        $this->assertInstanceOf('Sabre\\CalDAV\\Principal\\ProxyRead', $child);

    }

    function testGetChildProxyWrite(): void {

        $u = $this->getInstance();
        $child = $u->getChild('calendar-proxy-write');
        $this->assertInstanceOf('Sabre\\CalDAV\\Principal\\ProxyWrite', $child);

    }

    /**
     * @expectedException Sabre\DAV\Exception\NotFound
     */
    function testGetChildNotFound(): void {

        $u = $this->getInstance();
        $child = $u->getChild('foo');

    }

    /**
     * @expectedException Sabre\DAV\Exception\NotFound
     */
    function testGetChildNotFound2(): void {

        $u = $this->getInstance();
        $child = $u->getChild('random');

    }

    function testGetChildren(): void {

        $u = $this->getInstance();
        $children = $u->getChildren();
        $this->assertEquals(2, count($children));
        $this->assertInstanceOf('Sabre\\CalDAV\\Principal\\ProxyRead', $children[0]);
        $this->assertInstanceOf('Sabre\\CalDAV\\Principal\\ProxyWrite', $children[1]);

    }

    function testChildExist(): void {

        $u = $this->getInstance();
        $this->assertTrue($u->childExists('calendar-proxy-read'));
        $this->assertTrue($u->childExists('calendar-proxy-write'));
        $this->assertFalse($u->childExists('foo'));

    }

    function testGetACL(): void {

        $expected = array(
            array(
                'privilege' => '{DAV:}read',
                'principal' => '{DAV:}authenticated',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}read',
                'principal' => 'principals/user/calendar-proxy-read',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}read',
                'principal' => 'principals/user/calendar-proxy-write',
                'protected' => true,
            ),
        );

        $u = $this->getInstance();
        $this->assertEquals($expected, $u->getACL());

    }

}
