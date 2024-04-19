<?php

namespace Sabre\DAVACL\PrincipalBackend;

use Sabre\DAV;
use Sabre\HTTP;


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
    function testGetPrincipalsByPrefix(): void {

        $pdo = $this->getPDO();
        $backend = new PDO($pdo);

        $expected = array(
            array(
                'uri' => 'principals/user',
                '{http://sabredav.org/ns}email-address' => 'user@example.org',
                '{DAV:}displayname' => 'User',
            ),
            array(
                'uri' => 'principals/group',
                '{http://sabredav.org/ns}email-address' => 'group@example.org',
                '{DAV:}displayname' => 'Group',
            ),
        );

        $this->assertEquals($expected, $backend->getPrincipalsByPrefix('principals'));
        $this->assertEquals(array(), $backend->getPrincipalsByPrefix('foo'));

    }

    /**
     * @depends testConstruct
     */
    function testGetPrincipalByPath(): void {

        $pdo = $this->getPDO();
        $backend = new PDO($pdo);

        $expected = array(
            'id' => 1,
            'uri' => 'principals/user',
            '{http://sabredav.org/ns}email-address' => 'user@example.org',
            '{DAV:}displayname' => 'User',
        );

        $this->assertEquals($expected, $backend->getPrincipalByPath('principals/user'));
        $this->assertEquals(null, $backend->getPrincipalByPath('foo'));

    }

    function testGetGroupMemberSet(): void {

        $pdo = $this->getPDO();
        $backend = new PDO($pdo);
        $expected = array('principals/user');

        $this->assertEquals($expected,$backend->getGroupMemberSet('principals/group'));

    }

    function testGetGroupMembership(): void {

        $pdo = $this->getPDO();
        $backend = new PDO($pdo);
        $expected = array('principals/group');

        $this->assertEquals($expected,$backend->getGroupMembership('principals/user'));

    }

    function testSetGroupMemberSet(): void {

        $pdo = $this->getPDO();

        // Start situation
        $backend = new PDO($pdo);
        $this->assertEquals(array('principals/user'), $backend->getGroupMemberSet('principals/group'));

        // Removing all principals
        $backend->setGroupMemberSet('principals/group', array());
        $this->assertEquals(array(), $backend->getGroupMemberSet('principals/group'));

        // Adding principals again
        $backend->setGroupMemberSet('principals/group', array('principals/user'));
        $this->assertEquals(array('principals/user'), $backend->getGroupMemberSet('principals/group'));


    }

    function testSearchPrincipals(): void {

        $pdo = $this->getPDO();

        $backend = new PDO($pdo);

        $result = $backend->searchPrincipals('principals', array('{DAV:}blabla' => 'foo'));
        $this->assertEquals(array(), $result);

        $result = $backend->searchPrincipals('principals', array('{DAV:}displayname' => 'ou'));
        $this->assertEquals(array('principals/group'), $result);

        $result = $backend->searchPrincipals('principals', array('{DAV:}displayname' => 'UsEr', '{http://sabredav.org/ns}email-address' => 'USER@EXAMPLE'));
        $this->assertEquals(array('principals/user'), $result);

        $result = $backend->searchPrincipals('mom', array('{DAV:}displayname' => 'UsEr', '{http://sabredav.org/ns}email-address' => 'USER@EXAMPLE'));
        $this->assertEquals(array(), $result);

    }

    function testUpdatePrincipal(): void {

        $pdo = $this->getPDO();
        $backend = new PDO($pdo);

        $propPatch = new DAV\PropPatch([
            '{DAV:}displayname' => 'pietje',
        ]);

        $backend->updatePrincipal('principals/user', $propPatch);
        $result = $propPatch->commit();

        $this->assertTrue($result);

        $this->assertEquals(array(
            'id' => 1,
            'uri' => 'principals/user',
            '{DAV:}displayname' => 'pietje',
            '{http://sabredav.org/ns}email-address' => 'user@example.org',
        ), $backend->getPrincipalByPath('principals/user'));

    }

    function testUpdatePrincipalUnknownField(): void {

        $pdo = $this->getPDO();
        $backend = new PDO($pdo);

        $propPatch = new DAV\PropPatch([
            '{DAV:}displayname' => 'pietje',
            '{DAV:}unknown' => 'foo',
        ]);

        $backend->updatePrincipal('principals/user', $propPatch);
        $result = $propPatch->commit();

        $this->assertFalse($result);

        $this->assertEquals(array(
            '{DAV:}displayname' => 424,
            '{DAV:}unknown' => 403
        ), $propPatch->getResult());

        $this->assertEquals(array(
            'id' => '1',
            'uri' => 'principals/user',
            '{DAV:}displayname' => 'User',
            '{http://sabredav.org/ns}email-address' => 'user@example.org',
        ), $backend->getPrincipalByPath('principals/user'));

    }

}
