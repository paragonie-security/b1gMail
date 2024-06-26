<?php

namespace Sabre\VObject;

/**
 * Document
 *
 * A document is just like a component, except that it's also the top level
 * element.
 *
 * Both a VCALENDAR and a VCARD are considered documents.
 *
 * This class also provides a registry for document types.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
abstract class Document extends Component {

    /**
     * Unknown document type
     */
    const UNKNOWN = 1;

    /**
     * vCalendar 1.0
     */
    const VCALENDAR10 = 2;

    /**
     * iCalendar 2.0
     */
    const ICALENDAR20 = 3;

    /**
     * vCard 2.1
     */
    const VCARD21 = 4;

    /**
     * vCard 3.0
     */
    const VCARD30 = 5;

    /**
     * vCard 4.0
     */
    const VCARD40 = 6;

    /**
     * The default name for this component.
     *
     * This should be 'VCALENDAR' or 'VCARD'.
     *
     * @var string
     */
    static public $defaultName;

    /**
     * List of properties, and which classes they map to.
     *
     * @var array
     */
    static public $propertyMap = array();

    /**
     * List of components, along with which classes they map to.
     *
     * @var array
     */
    static public $componentMap = array();

    /**
     * List of value-types, and which classes they map to.
     *
     * @var array
     */
    static public $valueMap = array();

    /**
     * Creates a new document.
     *
     * We're changing the default behavior slightly here. First, we don't want
     * to have to specify a name (we already know it), and we want to allow
     * children to be specified in the first argument.
     *
     * But, the default behavior also works.
     *
     * So the two sigs:
     *
     * new Document(array $children = array(), $defaults = true);
     * new Document(string $name, array $children = array(), $defaults = true)
     *
     * @return void
     */
    public function __construct() {

        $args = func_get_args();
        if (count($args)===0 || is_array($args[0])) {
            array_unshift($args, $this, static::$defaultName);
            call_user_func_array(array('parent', '__construct'), $args);
        } else {
            array_unshift($args, $this);
            call_user_func_array(array('parent', '__construct'), $args);
        }

    }

    /**
     *
     * Returns the current document type.
     *
     * @psalm-return 1
     */
    public function getDocumentType(): int {

        return self::UNKNOWN;

    }





    /**
     * Factory method for creating new properties
     *
     * This method automatically searches for the correct property class, based
     * on its name.
     *
     * You can specify the parameters either in key=>value syntax, in which case
     * parameters will automatically be created, or you can just pass a list of
     * Parameter objects.
     *
     * @param string $name
     * @param mixed $value
     * @param array $parameters
     * @param string $valueType Force a specific valuetype, such as URI or TEXT
     * @return Property
     */
    public function createProperty($name, $value = null, array $parameters = null, $valueType = null) {

        // If there's a . in the name, it means it's prefixed by a groupname.
        if (($i=strpos($name,'.'))!==false) {
            $group = substr($name, 0, $i);
            $name = strtoupper(substr($name, $i+1));
        } else {
            $name = strtoupper($name);
            $group = null;
        }

        $class = null;

        if ($valueType) {
            // The valueType argument comes first to figure out the correct
            // class.
            $this->getClassNameForPropertyValue($valueType);
        }

        if (is_null($class) && isset($parameters['VALUE'])) {
            // If a VALUE parameter is supplied, we should use that.
            $this->getClassNameForPropertyValue($parameters['VALUE']);
        }
        if (is_null($class)) {
            $class = $this->getClassNameForPropertyName($name);
        }
        if (is_null($parameters)) $parameters = array();

        return new $class($this, $name, $value, $parameters, $group);

    }

    /**
     * This method returns a full class-name for a value parameter.
     *
     * For instance, DTSTART may have VALUE=DATE. In that case we will look in
     * our valueMap table and return the appropriate class name.
     *
     * This method returns null if we don't have a specialized class.
     *
     * @param string $valueParam
     * @return void
     */
    public function getClassNameForPropertyValue($valueParam) {

        $valueParam = strtoupper($valueParam);
        if (isset(static::$valueMap[$valueParam])) {
            return static::$valueMap[$valueParam];
        }

    }

    /**
     * Returns the default class for a property name.
     *
     * @param string $propertyName
     * @return string
     */
    public function getClassNameForPropertyName($propertyName) {

        if (isset(static::$propertyMap[$propertyName])) {
            return static::$propertyMap[$propertyName];
        } else {
            return 'Sabre\\VObject\\Property\\Unknown';
        }

    }

}
