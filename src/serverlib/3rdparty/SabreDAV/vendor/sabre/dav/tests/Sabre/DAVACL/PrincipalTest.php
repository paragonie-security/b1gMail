<?php

namespace Sabre\DAVACL;

use Sabre\DAV;
use Sabre\HTTP;

class PrincipalTest extends \PHPUnit_Framework_TestCase {

    public function testConstruct(): void {

        $principalBackend = new PrincipalBackend\Mock();
        $principal = new Principal($principalBackend, array('uri' => 'principals/admin'));
        $this->assertTrue($principal instanceof Principal);

    }

    /**
     * @expectedException Sabre\DAV\Exception
     */
    public function testConstructNoUri(): void {

        $principalBackend = new PrincipalBackend\Mock();
        $principal = new Principal($principalBackend, array());

    }

    public function testGetName(): void {

        $principalBackend = new PrincipalBackend\Mock();
        $principal = new Principal($principalBackend, array('uri' => 'principals/admin'));
        $this->assertEquals('admin',$principal->getName());

    }

    public function testGetDisplayName(): void {

        $principalBackend = new PrincipalBackend\Mock();
        $principal = new Principal($principalBackend, array('uri' => 'principals/admin'));
        $this->assertEquals('admin',$principal->getDisplayname());

        $principal = new Principal($principalBackend, array(
            'uri' => 'principals/admin',
            '{DAV:}displayname' => 'Mr. Admin'
        ));
        $this->assertEquals('Mr. Admin',$principal->getDisplayname());

    }

    public function testGetProperties(): void {

        $principalBackend = new PrincipalBackend\Mock();
        $principal = new Principal($principalBackend, array(
            'uri' => 'principals/admin',
            '{DAV:}displayname' => 'Mr. Admin',
            '{http://www.example.org/custom}custom' => 'Custom',
            '{http://sabredav.org/ns}email-address' => 'admin@example.org',
        ));

        $keys = array(
            '{DAV:}displayname',
            '{http://www.example.org/custom}custom',
            '{http://sabredav.org/ns}email-address',
        );
        $props = $principal->getProperties($keys);

        foreach($keys as $key) $this->assertArrayHasKey($key,$props);

        $this->assertEquals('Mr. Admin',$props['{DAV:}displayname']);

        $this->assertEquals('admin@example.org', $props['{http://sabredav.org/ns}email-address']);
    }

    public function testUpdateProperties(): void {

        $principalBackend = new PrincipalBackend\Mock();
        $principal = new Principal($principalBackend, array('uri' => 'principals/admin'));

        $propPatch = new DAV\PropPatch(array('{DAV:}yourmom' => 'test'));

        $result = $principal->propPatch($propPatch);
        $result = $propPatch->commit();
        $this->assertTrue($result);

    }

    public function testGetPrincipalUrl(): void {

        $principalBackend = new PrincipalBackend\Mock();
        $principal = new Principal($principalBackend, array('uri' => 'principals/admin'));
        $this->assertEquals('principals/admin',$principal->getPrincipalUrl());

    }

    public function testGetAlternateUriSet(): void {

        $principalBackend = new PrincipalBackend\Mock();
        $principal = new Principal($principalBackend, array(
            'uri' => 'principals/admin',
            '{DAV:}displayname' => 'Mr. Admin',
            '{http://www.example.org/custom}custom' => 'Custom',
            '{http://sabredav.org/ns}email-address' => 'admin@example.org',
            '{DAV:}alternate-URI-set' => array(
                'mailto:admin+1@example.org',
                'mailto:admin+2@example.org',
                'mailto:admin@example.org',
            ),
        ));

        $expected = array(
            'mailto:admin+1@example.org',
            'mailto:admin+2@example.org',
            'mailto:admin@example.org',
        );

        $this->assertEquals($expected,$principal->getAlternateUriSet());

    }
    public function testGetAlternateUriSetEmpty(): void {

        $principalBackend = new PrincipalBackend\Mock();
        $principal = new Principal($principalBackend, array(
            'uri' => 'principals/admin',
        ));

        $expected = array();

        $this->assertEquals($expected,$principal->getAlternateUriSet());

    }

    public function testGetGroupMemberSet(): void {

        $principalBackend = new PrincipalBackend\Mock();
        $principal = new Principal($principalBackend, array('uri' => 'principals/admin'));
        $this->assertEquals(array(),$principal->getGroupMemberSet());

    }
    public function testGetGroupMembership(): void {

        $principalBackend = new PrincipalBackend\Mock();
        $principal = new Principal($principalBackend, array('uri' => 'principals/admin'));
        $this->assertEquals(array(),$principal->getGroupMembership());

    }

    public function testSetGroupMemberSet(): void {

        $principalBackend = new PrincipalBackend\Mock();
        $principal = new Principal($principalBackend, array('uri' => 'principals/admin'));
        $principal->setGroupMemberSet(array('principals/foo'));

        $this->assertEquals(array(
            'principals/admin' => array('principals/foo'),
        ), $principalBackend->groupMembers);

    }

    public function testGetOwner(): void {

        $principalBackend = new PrincipalBackend\Mock();
        $principal = new Principal($principalBackend, array('uri' => 'principals/admin'));
        $this->assertEquals('principals/admin',$principal->getOwner());

    }

    public function testGetGroup(): void {

        $principalBackend = new PrincipalBackend\Mock();
        $principal = new Principal($principalBackend, array('uri' => 'principals/admin'));
        $this->assertNull($principal->getGroup());

    }

    public function testGetACl(): void {

        $principalBackend = new PrincipalBackend\Mock();
        $principal = new Principal($principalBackend, array('uri' => 'principals/admin'));
        $this->assertEquals(array(
            array(
                'privilege' => '{DAV:}read',
                'principal' => '{DAV:}authenticated',
                'protected' => true,
            )
        ),$principal->getACL());

    }

    /**
     * @expectedException Sabre\DAV\Exception\MethodNotAllowed
     */
    public function testSetACl(): void {

        $principalBackend = new PrincipalBackend\Mock();
        $principal = new Principal($principalBackend, array('uri' => 'principals/admin'));
        $principal->setACL(array());

    }

    public function testGetSupportedPrivilegeSet(): void {

        $principalBackend = new PrincipalBackend\Mock();
        $principal = new Principal($principalBackend, array('uri' => 'principals/admin'));
        $this->assertNull($principal->getSupportedPrivilegeSet());

    }

}
