<?php

namespace Sabre\CardDAV\Xml\Property;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;
use Sabre\CardDAV\Plugin;

/**
 * Supported-address-data property
 *
 * This property is a representation of the supported-address-data property
 * in the CardDAV namespace.
 *
 * This property is defined in:
 *
 * http://tools.ietf.org/html/rfc6352#section-6.2.2
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class SupportedAddressData implements XmlSerializable {

    /**
     * supported versions
     *
     * @var array
     */
    protected $supportedData = [];

    /**
     * Creates the property
     *
     * @param array|null $supportedData
     */
    function __construct(array $supportedData = null) {

        if (is_null($supportedData)) {
            $supportedData = [
                ['contentType' => 'text/vcard', 'version' => '3.0'],
                ['contentType' => 'text/vcard', 'version' => '4.0'],
                ['contentType' => 'application/vcard+json', 'version' => '4.0'],
            ];
        }

        $this->supportedData = $supportedData;

    }

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

        foreach ($this->supportedData as $supported) {
            $writer->startElement('{' . Plugin::NS_CARDDAV . '}address-data-type');
            $writer->writeAttributes([
                'content-type' => $supported['contentType'],
                'version'      => $supported['version']
                ]);
            $writer->endElement(); // address-data-type
        }

    }

}
