<?php

namespace Sabre\CalDAV\Exception;

use Sabre\DAV;
use Sabre\CalDAV;

/**
 * InvalidComponentType
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class InvalidComponentType extends DAV\Exception\Forbidden {

    /**
     * Adds in extra information in the xml response.
     *
     * This method adds the {CALDAV:}supported-calendar-component as defined in rfc4791
     *
     * @param DAV\Server $server
     * @param \DOMElement $errorNode
     * @return void
     */
    function serialize(DAV\Server $server, \DOMElement $errorNode): void {

        $doc = $errorNode->ownerDocument;

        $np = $doc->createElementNS(CalDAV\Plugin::NS_CALDAV, 'cal:supported-calendar-component');
        $errorNode->appendChild($np);

    }

}
