<?php

namespace Sabre\CardDAV;

use Sabre\DAVACL;
use Sabre\DAV;

/**
 * The Card object represents a single Card from an addressbook
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Card extends DAV\File implements ICard, DAVACL\IACL {

    /**
     * CardDAV backend
     *
     * @var Backend\BackendInterface
     */
    protected $carddavBackend;

    /**
     * Array with information about this Card
     *
     * @var array
     */
    protected $cardData;

    /**
     * Array with information about the containing addressbook
     *
     * @var array
     */
    protected $addressBookInfo;

    /**
     * Constructor
     *
     * @param Backend\BackendInterface $carddavBackend
     * @param array $addressBookInfo
     * @param array $cardData
     */
    function __construct(Backend\BackendInterface $carddavBackend, array $addressBookInfo, array $cardData) {

        $this->carddavBackend = $carddavBackend;
        $this->addressBookInfo = $addressBookInfo;
        $this->cardData = $cardData;

    }

    /**
     * Returns the uri for this object
     *
     * @return string
     */
    function getName() {

        return $this->cardData['uri'];

    }

    /**
     * Returns the VCard-formatted object
     *
     * @return string
     */
    function get() {

        // Pre-populating 'carddata' is optional. If we don't yet have it
        // already, we fetch it from the backend.
        if (!isset($this->cardData['carddata'])) {
            $this->cardData = $this->carddavBackend->getCard($this->addressBookInfo['id'], $this->cardData['uri']);
        }
        return $this->cardData['carddata'];

    }

    /**
     * Updates the VCard-formatted object
     *
     * @param string $cardData
     * @return string|null
     */
    function put($cardData) {

        if (is_resource($cardData))
            $cardData = stream_get_contents($cardData);

        // Converting to UTF-8, if needed
        $cardData = DAV\StringUtil::ensureUTF8($cardData);

        $etag = $this->carddavBackend->updateCard($this->addressBookInfo['id'], $this->cardData['uri'], $cardData);
        $this->cardData['carddata'] = $cardData;
        $this->cardData['etag'] = $etag;

        return $etag;

    }

    /**
     * Deletes the card
     *
     * @return void
     */
    function delete(): void {

        $this->carddavBackend->deleteCard($this->addressBookInfo['id'], $this->cardData['uri']);

    }



    /**
     * Returns an ETag for this object
     *
     * @return string
     */
    function getETag() {

        if (isset($this->cardData['etag'])) {
            return $this->cardData['etag'];
        } else {
            $data = $this->get();
            if (is_string($data)) {
                return '"' . md5($data) . '"';
            } else {
                // We refuse to calculate the md5 if it's a stream.
                return null;
            }
        }

    }

    /**
     * Returns the last modification date as a unix timestamp
     *
     * @return int
     */
    function getLastModified() {

        return isset($this->cardData['lastmodified']) ? $this->cardData['lastmodified'] : null;

    }



    /**
     * Returns the owner principal
     *
     * This must be a url to a principal, or null if there's no owner
     *
     * @return string|null
     */
    function getOwner() {

        return $this->addressBookInfo['principaluri'];

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

        // An alternative acl may be specified through the cardData array.
        if (isset($this->cardData['acl'])) {
            return $this->cardData['acl'];
        }

        return [
            [
                'privilege' => '{DAV:}read',
                'principal' => $this->addressBookInfo['principaluri'],
                'protected' => true,
            ],
            [
                'privilege' => '{DAV:}write',
                'principal' => $this->addressBookInfo['principaluri'],
                'protected' => true,
            ],
        ];

    }

    /**
     * Updates the ACL
     *
     * This method will receive a list of new ACE's.
     *
     * @param array $acl
     * @return void
     */
    function setACL(array $acl) {

        throw new DAV\Exception\MethodNotAllowed('Changing ACL is not yet supported');

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
