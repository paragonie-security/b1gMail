<?php

namespace Sabre\CalDAV\Xml\Property;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;
use Sabre\CalDAV\Plugin;

/**
 * supported-collation-set property
 *
 * This property is a representation of the supported-collation-set property
 * in the CalDAV namespace.
 *
 * This property is defined in:
 * http://tools.ietf.org/html/rfc4791#section-7.5.1
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class SupportedCollationSet implements XmlSerializable {

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

        $collations = [
            'i;ascii-casemap',
            'i;octet',
            'i;unicode-casemap'
        ];

        foreach ($collations as $collation) {
            $writer->writeElement('{' . Plugin::NS_CALDAV . '}supported-collation', $collation);
        }

    }

}
