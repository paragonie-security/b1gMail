<?php

namespace Sabre\CalDAV\Backend;

/**
 * Implementing this interface adds CalDAV Scheduling support to your caldav
 * server, as defined in rfc6638.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
interface SchedulingSupport extends BackendInterface {

    /**
     * Returns a single scheduling object for the inbox collection.
     *
     * The returned array should contain the following elements:
     *   * uri - A unique basename for the object. This will be used to
     *           construct a full uri.
     *   * calendardata - The iCalendar object
     *   * lastmodified - The last modification date. Can be an int for a unix
     *                    timestamp, or a PHP DateTime object.
     *   * etag - A unique token that must change if the object changed.
     *   * size - The size of the object, in bytes.
     *
     * @param string $principalUri
     * @param string $objectUri
     * @return array
     */
    function getSchedulingObject($principalUri, $objectUri);



    /**
     * Deletes a scheduling object from the inbox collection.
     *
     * @param string $principalUri
     * @param string $objectUri
     * @return void
     */
    function deleteSchedulingObject($principalUri, $objectUri);



}
