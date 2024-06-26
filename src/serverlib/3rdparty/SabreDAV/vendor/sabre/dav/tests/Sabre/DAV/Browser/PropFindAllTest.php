<?php

namespace Sabre\DAV\Browser;

class PropFindAllTest extends \PHPUnit_Framework_TestCase {

    function testHandleSimple(): void {

        $pf = new PropFindAll('foo');
        $pf->handle('{DAV:}displayname', 'foo');

        $this->assertEquals(200, $pf->getStatus('{DAV:}displayname'));
        $this->assertEquals('foo', $pf->get('{DAV:}displayname'));
           

    }

    function testHandleCallBack(): void {

        $pf = new PropFindAll('foo');
        $pf->handle('{DAV:}displayname', function() { return 'foo'; });

        $this->assertEquals(200, $pf->getStatus('{DAV:}displayname'));
        $this->assertEquals('foo', $pf->get('{DAV:}displayname'));

    }

    function testSet(): void {

        $pf = new PropFindAll('foo');
        $pf->set('{DAV:}displayname', 'foo');

        $this->assertEquals(200, $pf->getStatus('{DAV:}displayname'));
        $this->assertEquals('foo', $pf->get('{DAV:}displayname'));

    }

    function testSetNull(): void {

        $pf = new PropFindAll('foo');
        $pf->set('{DAV:}displayname', null);

        $this->assertEquals(404, $pf->getStatus('{DAV:}displayname'));
        $this->assertEquals(null, $pf->get('{DAV:}displayname'));

    }

    function testGet404Properties(): void {

        $pf = new PropFindAll('foo');
        $pf->set('{DAV:}displayname', null);
        $this->assertEquals(
            ['{DAV:}displayname'],
            $pf->get404Properties()
        );

    }

    function testGet404PropertiesNothing(): void {

        $pf = new PropFindAll('foo');
        $pf->set('{DAV:}displayname', 'foo');
        $this->assertEquals(
            ['{http://sabredav.org/ns}idk'],
            $pf->get404Properties()
        );

    }

}
