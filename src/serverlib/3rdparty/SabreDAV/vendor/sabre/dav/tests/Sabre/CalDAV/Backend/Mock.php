<?php

namespace Sabre\CalDAV\Backend;
use Sabre\DAV;
use Sabre\CalDAV;

class Mock extends AbstractBackend {

    protected $calendarData;
    protected $calendars;

    function __construct(array $calendars = [], array $calendarData = []) {

        foreach($calendars as &$calendar) {
            if (!isset($calendar['id'])) {
                $calendar['id'] = DAV\UUIDUtil::getUUID();
            }
        }

        $this->calendars = $calendars;
        $this->calendarData = $calendarData;

    }



    /**
     * Creates a new calendar for a principal.
     *
     * If the creation was a success, an id must be returned that can be used to reference
     * this calendar in other methods, such as updateCalendar.
     *
     * This function must return a server-wide unique id that can be used
     * later to reference the calendar.
     *
     * @param string $principalUri
     * @param string $calendarUri
     * @param array $properties
     * @return string|int
     */
    function createCalendar($principalUri,$calendarUri,array $properties) {

        $id = DAV\UUIDUtil::getUUID();
        $this->calendars[] = array_merge([
            'id' => $id,
            'principaluri' => $principalUri,
            'uri' => $calendarUri,
            '{' . CalDAV\Plugin::NS_CALDAV . '}supported-calendar-component-set' => new CalDAV\Xml\Property\SupportedCalendarComponentSet(['VEVENT','VTODO']),
        ], $properties);

        return $id;

    }

    /**
     * Delete a calendar and all it's objects
     *
     * @param string $calendarId
     * @return void
     */
    public function deleteCalendar($calendarId): void {

        foreach($this->calendars as $k=>$calendar) {
            if ($calendar['id'] === $calendarId) {
                unset($this->calendars[$k]);
            }
        }

    }

    /**
     * Returns all calendar objects within a calendar object.
     *
     * Every item contains an array with the following keys:
     *   * id - unique identifier which will be used for subsequent updates
     *   * calendardata - The iCalendar-compatible calendar data
     *   * uri - a unique key which will be used to construct the uri. This can be any arbitrary string.
     *   * lastmodified - a timestamp of the last modification time
     *   * etag - An arbitrary string, surrounded by double-quotes. (e.g.:
     *   '  "abcdef"')
     *   * calendarid - The calendarid as it was passed to this function.
     *
     * Note that the etag is optional, but it's highly encouraged to return for
     * speed reasons.
     *
     * The calendardata is also optional. If it's not returned
     * 'getCalendarObject' will be called later, which *is* expected to return
     * calendardata.
     *
     * @param string $calendarId
     * @return array
     */
    public function getCalendarObjects($calendarId) {

        if (!isset($this->calendarData[$calendarId]))
            return array();

        $objects = $this->calendarData[$calendarId];

        foreach($objects as $uri => &$object) {
            $object['calendarid'] = $calendarId;
            $object['uri'] = $uri;
            $object['lastmodified'] = null;
        }
        return $objects;

    }

    /**
     * Returns information from a single calendar object, based on it's object
     * uri.
     *
     * The returned array must have the same keys as getCalendarObjects. The
     * 'calendardata' object is required here though, while it's not required
     * for getCalendarObjects.
     *
     * @param string $calendarId
     * @param string $objectUri
     * @return array
     */
    function getCalendarObject($calendarId,$objectUri) {

        if (!isset($this->calendarData[$calendarId][$objectUri])) {
            throw new DAV\Exception\NotFound('Object could not be found');
        }
        $object = $this->calendarData[$calendarId][$objectUri];
        $object['calendarid'] = $calendarId;
        $object['uri'] = $objectUri;
        $object['lastmodified'] = null;
        return $object;

    }

    /**
     *
     * Creates a new calendar object.
     *
     * @param string $calendarId
     * @param string $objectUri
     * @param string $calendarData
     */
    function createCalendarObject($calendarId,$objectUri,$calendarData): string {

        $this->calendarData[$calendarId][$objectUri] = array(
            'calendardata' => $calendarData,
            'calendarid' => $calendarId,
            'uri' => $objectUri,
        );
        return '"' . md5($calendarData) . '"';

    }





}
