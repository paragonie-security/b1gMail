<?php

namespace Sabre\DAVACL\Xml\Property;

use Sabre\Xml\XmlSerializable;
use Sabre\Xml\Writer;

/**
 * AclRestrictions property
 *
 * This property represents {DAV:}acl-restrictions, as defined in RFC3744.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class AclRestrictions implements XmlSerializable {

    /**
     * The xmlSerialize metod is called during xml writing.
     *
     * Use the $writer argument to write its own xml serialization.
     *
     * An important note: do _not_ create a parent element. Any element
     * implementing XmlSerializble should only ever write what's considered
     * its 'inner xml'.
     *
     * The parent of the current element is responsible for writing a
     * containing element.
     *
     * This allows serializers to be re-used for different element names.
     *
     * If you are opening new elements, you must also close them again.
     *
     * @param Writer $writer
     * @return void
     */
    function xmlSerialize(Writer $writer): void {

        $writer->writeElement('{DAV:}grant-only');
        $writer->writeElement('{DAV:}no-invert');

    }

}
