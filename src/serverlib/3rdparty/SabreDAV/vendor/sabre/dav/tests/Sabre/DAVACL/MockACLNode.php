<?php

namespace Sabre\DAVACL;

use Sabre\DAV;
use Sabre\HTTP;

class MockACLNode extends DAV\Node implements IACL {

    public $name;
    public $acl;

    function __construct($name, array $acl = array()) {

        $this->name = $name;
        $this->acl = $acl;

    }

    function getName() {

        return $this->name;

    }

    function getOwner() {

        return null;

    }

    function getGroup() {

        return null;

    }

    function getACL() {

        return $this->acl;

    }

    function setACL(array $acl): void {

        $this->acl = $acl;

    }

    function getSupportedPrivilegeSet() {

        return null;

    }

}
