<?php

namespace Sabre;

use
    Sabre\HTTP\Request,
    Sabre\HTTP\Response,
    Sabre\HTTP\Sapi;

/**
 * This class may be used as a basis for other webdav-related unittests.
 *
 * This class is supposed to provide a reasonably big framework to quickly get
 * a testing environment running.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
abstract class DAVServerTest extends \PHPUnit_Framework_TestCase {

    protected $setupCalDAV = false;
    protected $setupCardDAV = false;
    protected $setupACL = false;
    protected $setupCalDAVSharing = false;
    protected $setupCalDAVScheduling = false;
    protected $setupCalDAVSubscriptions = false;
    protected $setupCalDAVICSExport = false;
    protected $setupLocks = false;
    protected $setupFiles = false;

    /**
     * An array with calendars. Every calendar should have
     *   - principaluri
     *   - uri
     */
    protected $caldavCalendars = array();
    protected $caldavCalendarObjects = array();

    protected $carddavAddressBooks = array();
    protected $carddavCards = array();

    /**
     * @var Sabre\DAV\Server
     */
    protected $server;
    protected $tree = array();

    protected $caldavBackend;
    protected $carddavBackend;
    protected $principalBackend;
    protected $locksBackend;

    /**
     * @var Sabre\CalDAV\Plugin
     */
    protected $caldavPlugin;

    /**
     * @var Sabre\CardDAV\Plugin
     */
    protected $carddavPlugin;

    /**
     * @var Sabre\DAVACL\Plugin
     */
    protected $aclPlugin;

    /**
     * @var Sabre\CalDAV\SharingPlugin
     */
    protected $caldavSharingPlugin;

    /**
     * CalDAV scheduling plugin
     *
     * @var CalDAV\Schedule\Plugin
     */
    protected $caldavSchedulePlugin;

    /**
     * @var Sabre\DAV\Auth\Plugin
     */
    protected $authPlugin;

    /**
     * @var Sabre\DAV\Locks\Plugin
     */
    protected $locksPlugin;

    /**
     * If this string is set, we will automatically log in the user with this
     * name.
     */
    protected $autoLogin = null;












}
