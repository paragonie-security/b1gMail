<?php

namespace Sabre\DAVACL\FS;

use Sabre\DAV\FSExt\Directory as BaseCollection;
use Sabre\DAVACL\IACL;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;

/**
 * This is an ACL-enabled collection.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Collection extends BaseCollection implements IACL {

    /**
     * A list of ACL rules.
     *
     * @var array
     */
    protected $acl;

    /**
     * Owner uri, or null for no owner.
     *
     * @var string|null
     */
    protected $owner;

    /**
     * Constructor
     *
     * @param string $path on-disk path.
     * @param array $acl ACL rules.
     * @param string|null $owner principal owner string.
     */
    function __construct($path, array $acl, $owner = null) {

        parent::__construct($path);
        $this->acl = $acl;
        $this->owner = $owner;

    }

    /**
     *
     * Returns a specific child node, referenced by its name
     *
     * This method must throw Sabre\DAV\Exception\NotFound if the node does not
     * exist.
     *
     * @param string $name
     *
     * @throws DAV\Exception\NotFound
     */
    function getChild($name): File|self {

        $path = $this->path . '/' . $name;

        if (!file_exists($path)) throw new NotFound('File could not be located');
        if ($name == '.' || $name == '..') throw new Forbidden('Permission denied to . and ..');

        if (is_dir($path)) {

            return new self($path, $this->acl, $this->owner);

        } else {

            return new File($path, $this->acl, $this->owner);

        }

    }

    /**
     * Returns the owner principal
     *
     * This must be a url to a principal, or null if there's no owner
     *
     * @return string|null
     */
    function getOwner() {

        return $this->owner;

    }

    /**
     * Returns a group principal
     *
     * This must be a url to a principal, or null if there's no owner
     *
     * @return string|null
     */
    function getGroup() {

        return null;

    }

    /**
     * Returns a list of ACE's for this node.
     *
     * Each ACE has the following properties:
     *   * 'privilege', a string such as {DAV:}read or {DAV:}write. These are
     *     currently the only supported privileges
     *   * 'principal', a url to the principal who owns the node
     *   * 'protected' (optional), indicating that this ACE is not allowed to
     *      be updated.
     *
     * @return array
     */
    function getACL() {

        return $this->acl;

    }

    /**
     * Updates the ACL
     *
     * This method will receive a list of new ACE's as an array argument.
     *
     * @param array $acl
     * @return void
     */
    function setACL(array $acl) {

        throw new Forbidden('Setting ACL is not allowed here');

    }

    /**
     * Returns the list of supported privileges for this node.
     *
     * The returned data structure is a list of nested privileges.
     * See Sabre\DAVACL\Plugin::getDefaultSupportedPrivilegeSet for a simple
     * standard structure.
     *
     * If null is returned from this method, the default privilege set is used,
     * which is fine for most common usecases.
     *
     * @return array|null
     */
    function getSupportedPrivilegeSet() {

        return null;

    }

}
