<?php

namespace Sabre\VObject\Parser;

use
    Sabre\VObject\ParseException,
    Sabre\VObject\EofException,
    Sabre\VObject\Component,
    Sabre\VObject\Property,
    Sabre\VObject\Component\VCalendar,
    Sabre\VObject\Component\VCard;

/**
 * MimeDir parser.
 *
 * This class parses iCalendar 2.0 and vCard 2.1, 3.0 and 4.0 files. This
 * parser will return one of the following two objects from the parse method:
 *
 * Sabre\VObject\Component\VCalendar
 * Sabre\VObject\Component\VCard
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class MimeDir extends Parser {

    /**
     * The input stream.
     *
     * @var resource
     */
    protected $input;

    /**
     * Root component
     *
     * @var Component
     */
    protected $root;

    /**
     *
     * Parses an iCalendar or vCard file
     *
     * Pass a stream or a string. If null is parsed, the existing buffer is
     * used.
     *
     * @param string|resource|null $input
     * @param int|null $options
     */
    public function parse($input = null, $options = null): Component {

        $this->root = null;
        if (!is_null($input)) {

            $this->setInput($input);

        }

        if (!is_null($options)) $this->options = $options;

        $this->parseDocument();

        return $this->root;

    }

    /**
     * Sets the input buffer. Must be a string or stream.
     *
     * @param resource|string $input
     * @return void
     */
    public function setInput($input): void {

        // Resetting the parser
        $this->lineIndex = 0;
        $this->startLine = 0;

        if (is_string($input)) {
            // Convering to a stream.
            $stream = fopen('php://temp', 'r+');
            fwrite($stream, $input);
            rewind($stream);
            $this->input = $stream;
        } elseif (is_resource($input)) {
            $this->input = $input;
        } else {
            throw new \InvalidArgumentException('This parser can only read from strings or streams.');
        }

    }

    /**
     * Parses an entire document.
     *
     * @return void
     */
    protected function parseDocument() {

        $line = $this->readLine();

        // BOM is ZERO WIDTH NO-BREAK SPACE (U+FEFF).
        // It's 0xEF 0xBB 0xBF in UTF-8 hex.
        if (   3 <= strlen($line)
            && ord($line[0]) === 0xef
            && ord($line[1]) === 0xbb
            && ord($line[2]) === 0xbf) {
            $line = substr($line, 3);
        }

        switch(strtoupper($line)) {
            case 'BEGIN:VCALENDAR' :
                $class = isset(VCalendar::$componentMap['VCALENDAR'])
                    ? VCalendar::$componentMap[$name]
                    : 'Sabre\\VObject\\Component\\VCalendar';
                break;
            case 'BEGIN:VCARD' :
                $class = isset(VCard::$componentMap['VCARD'])
                    ? VCard::$componentMap['VCARD']
                    : 'Sabre\\VObject\\Component\\VCard';
                break;
            default :
                throw new ParseException('This parser only supports VCARD and VCALENDAR files');
        }

        $this->root = new $class(array(), false);

        while(true) {

            // Reading until we hit END:
            $line = $this->readLine();
            if (strtoupper(substr($line,0,4)) === 'END:') {
                break;
            }
            $result = $this->parseLine($line);
            if ($result) {
                $this->root->add($result);
            }

        }

        $name = strtoupper(substr($line, 4));
        if ($name!==$this->root->name) {
            throw new ParseException('Invalid MimeDir file. expected: "END:' . $this->root->name . '" got: "END:' . $name . '"');
        }

    }

    /**
     * Parses a line, and if it hits a component, it will also attempt to parse
     * the entire component
     *
     * @param string $line Unfolded line
     * @return Node
     */
    protected function parseLine($line) {

        // Start of a new component
        if (strtoupper(substr($line, 0, 6)) === 'BEGIN:') {

            $component = $this->root->createComponent(substr($line,6), array(), false);

            while(true) {

                // Reading until we hit END:
                $line = $this->readLine();
                if (strtoupper(substr($line,0,4)) === 'END:') {
                    break;
                }
                $result = $this->parseLine($line);
                if ($result) {
                    $component->add($result);
                }

            }

            $name = strtoupper(substr($line, 4));
            if ($name!==$component->name) {
                throw new ParseException('Invalid MimeDir file. expected: "END:' . $component->name . '" got: "END:' . $name . '"');
            }

            return $component;

        } else {

            // Property reader
            $this->readProperty($line);
            if (!$property) {
                // Ignored line
                return false;
            }
            return $property;

        }

    }

    /**
     * We need to look ahead 1 line every time to see if we need to 'unfold'
     * the next line.
     *
     * If that was not the case, we store it here.
     *
     * @var null|string
     */
    protected $lineBuffer;

    /**
     * The real current line number.
     */
    protected $lineIndex = 0;

    /**
     * In the case of unfolded lines, this property holds the line number for
     * the start of the line.
     *
     * @var int
     */
    protected $startLine = 0;

    /**
     * Contains a 'raw' representation of the current line.
     *
     * @var string
     */
    protected $rawLine;

    /**
     * Reads a single line from the buffer.
     *
     * This method strips any newlines and also takes care of unfolding.
     *
     * @throws \Sabre\VObject\EofException
     * @return string
     */
    protected function readLine() {

        if (!is_null($this->lineBuffer)) {
            $rawLine = $this->lineBuffer;
            $this->lineBuffer = null;
        } else {
            do {
                $eof = feof($this->input);

                $rawLine = fgets($this->input);

                if ($eof || (feof($this->input) && $rawLine===false)) {
                    throw new EofException('End of document reached prematurely');
                }
                if ($rawLine === false) {
                    throw new ParseException('Error reading from input stream');
                }
                $rawLine = rtrim($rawLine, "\r\n");
            } while ($rawLine === ''); // Skipping empty lines
            $this->lineIndex++;
        }
        $line = $rawLine;

        $this->startLine = $this->lineIndex;

        // Looking ahead for folded lines.
        while (true) {

            $nextLine = rtrim(fgets($this->input), "\r\n");
            $this->lineIndex++;
            if (!$nextLine) {
                break;
            }
            if ($nextLine[0] === "\t" || $nextLine[0] === " ") {
                $line .= substr($nextLine, 1);
                $rawLine .= "\n " . substr($nextLine, 1);
            } else {
                $this->lineBuffer = $nextLine;
                break;
            }

        }
        $this->rawLine = $rawLine;
        return $line;

    }

    /**
     * Reads a property or component from a line.
     *
     * @return void
     */
    protected function readProperty(string $line) {

        if ($this->options & self::OPTION_FORGIVING) {
            $propNameToken = 'A-Z0-9\-\._\\/';
        } else {
            $propNameToken = 'A-Z0-9\-\.';
        }

        $paramNameToken = 'A-Z0-9\-';
        $safeChar = '^";:,';
        $qSafeChar = '^"';

        $regex = "/
            ^(?P<name> [$propNameToken]+ ) (?=[;:])        # property name
            |
            (?<=:)(?P<propValue> .+)$                      # property value
            |
            ;(?P<paramName> [$paramNameToken]+) (?=[=;:])  # parameter name
            |
            (=|,)(?P<paramValue>                           # parameter value
                (?: [$safeChar]*) |
                \"(?: [$qSafeChar]+)\"
            ) (?=[;:,])
            /xi";

        //echo $regex, "\n"; die();
        preg_match_all($regex, $line, $matches,  PREG_SET_ORDER);

        $property = array(
            'name' => null,
            'parameters' => array(),
            'value' => null
        );

        $lastParam = null;

        /**
         * Looping through all the tokens.
         *
         * Note that we are looping through them in reverse order, because if a
         * sub-pattern matched, the subsequent named patterns will not show up
         * in the result.
         */
        foreach($matches as $match) {

            if (isset($match['paramValue'])) {
                if ($match['paramValue'] && $match['paramValue'][0] === '"') {
                    $value = substr($match['paramValue'], 1, -1);
                } else {
                    $value = $match['paramValue'];
                }

                $value = $this->unescapeParam($value);

                if (is_null($property['parameters'][$lastParam])) {
                    $property['parameters'][$lastParam] = $value;
                } elseif (is_array($property['parameters'][$lastParam])) {
                    $property['parameters'][$lastParam][] = $value;
                } else {
                    $property['parameters'][$lastParam] = array(
                        $property['parameters'][$lastParam],
                        $value
                    );
                }
                continue;
            }
            if (isset($match['paramName'])) {
                $lastParam = strtoupper($match['paramName']);
                if (!isset($property['parameters'][$lastParam])) {
                    $property['parameters'][$lastParam] = null;
                }
                continue;
            }
            if (isset($match['propValue'])) {
                $property['value'] = $match['propValue'];
                continue;
            }
            if (isset($match['name']) && $match['name']) {
                $property['name'] = strtoupper($match['name']);
                continue;
            }

            // @codeCoverageIgnoreStart
            throw new \LogicException('This code should not be reachable');
            // @codeCoverageIgnoreEnd

        }

        if (is_null($property['value'])) {
            $property['value'] = '';
        }
        if (!$property['name']) {
            if ($this->options & self::OPTION_IGNORE_INVALID_LINES) {
                return false;
            }
            throw new ParseException('Invalid Mimedir file. Line starting at ' . $this->startLine . ' did not follow iCalendar/vCard conventions');
        }

        // vCard 2.1 states that parameters may appear without a name, and only
        // a value. We can deduce the value based on it's name.
        //
        // Our parser will get those as parameters without a value instead, so
        // we're filtering these parameters out first.
        $namedParameters = array();
        $namelessParameters = array();

        foreach($property['parameters'] as $name=>$value) {
            if (!is_null($value)) {
                $namedParameters[$name] = $value;
            } else {
                $namelessParameters[] = $name;
            }
        }

        $propObj = $this->root->createProperty($property['name'], null, $namedParameters);

        foreach($namelessParameters as $namelessParameter) {
            $propObj->add(null, $namelessParameter);
        }

        if (strtoupper($propObj['ENCODING']) === 'QUOTED-PRINTABLE') {
            $propObj->setQuotedPrintableValue($this->extractQuotedPrintableValue());
        } else {
            $propObj->setRawMimeDirValue($property['value']);
        }

        return $propObj;

    }

    /**
     * Unescapes a property value.
     *
     * vCard 2.1 says:
     *   * Semi-colons must be escaped in some property values, specifically
     *     ADR, ORG and N.
     *   * Semi-colons must be escaped in parameter values, because semi-colons
     *     are also use to separate values.
     *   * No mention of escaping backslashes with another backslash.
     *   * newlines are not escaped either, instead QUOTED-PRINTABLE is used to
     *     span values over more than 1 line.
     *
     * vCard 3.0 says:
     *   * (rfc2425) Backslashes, newlines (\n or \N) and comma's must be
     *     escaped, all time time.
     *   * Comma's are used for delimeters in multiple values
     *   * (rfc2426) Adds to to this that the semi-colon MUST also be escaped,
     *     as in some properties semi-colon is used for separators.
     *   * Properties using semi-colons: N, ADR, GEO, ORG
     *   * Both ADR and N's individual parts may be broken up further with a
     *     comma.
     *   * Properties using commas: NICKNAME, CATEGORIES
     *
     * vCard 4.0 (rfc6350) says:
     *   * Commas must be escaped.
     *   * Semi-colons may be escaped, an unescaped semi-colon _may_ be a
     *     delimiter, depending on the property.
     *   * Backslashes must be escaped
     *   * Newlines must be escaped as either \N or \n.
     *   * Some compound properties may contain multiple parts themselves, so a
     *     comma within a semi-colon delimited property may also be unescaped
     *     to denote multiple parts _within_ the compound property.
     *   * Text-properties using semi-colons: N, ADR, ORG, CLIENTPIDMAP.
     *   * Text-properties using commas: NICKNAME, RELATED, CATEGORIES, PID.
     *
     * Even though the spec says that commas must always be escaped, the
     * example for GEO in Section 6.5.2 seems to violate this.
     *
     * iCalendar 2.0 (rfc5545) says:
     *   * Commas or semi-colons may be used as delimiters, depending on the
     *     property.
     *   * Commas, semi-colons, backslashes, newline (\N or \n) are always
     *     escaped, unless they are delimiters.
     *   * Colons shall not be escaped.
     *   * Commas can be considered the 'default delimiter' and is described as
     *     the delimiter in cases where the order of the multiple values is
     *     insignificant.
     *   * Semi-colons are described as the delimiter for 'structured values'.
     *     They are specifically used in Semi-colons are used as a delimiter in
     *     REQUEST-STATUS, RRULE, GEO and EXRULE. EXRULE is deprecated however.
     *
     * Now for the parameters
     *
     * If delimiter is not set (null) this method will just return a string.
     * If it's a comma or a semi-colon the string will be split on those
     * characters, and always return an array.
     *
     * @param string $input
     * @param string $delimiter
     * @return string|string[]
     */
    static public function unescapeValue($input, $delimiter = ';') {

        $regex = '#  (?: (\\\\ (?: \\\\ | N | n | ; | , ) )';
        if ($delimiter) {
            $regex .= ' | (' . $delimiter . ')';
        }
        $regex .= ') #x';

        $matches = preg_split($regex, $input, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $resultArray = array();
        $result = '';

        foreach($matches as $match) {

            switch ($match) {
                case '\\\\' :
                    $result .='\\';
                    break;
                case '\N' :
                case '\n' :
                    $result .="\n";
                    break;
                case '\;' :
                    $result .=';';
                    break;
                case '\,' :
                    $result .=',';
                    break;
                case $delimiter :
                    $resultArray[] = $result;
                    $result = '';
                    break;
                default :
                    $result .= $match;
                    break;

            }

        }

        $resultArray[] = $result;
        return $delimiter ? $resultArray : $result;

    }

    /**
     *
     * Unescapes a parameter value.
     *
     * vCard 2.1:
     * Does not mention a mechanism for this. In addition, double quotes
     * are never used to wrap values.
     * This means that parameters can simply not contain colons or
     * semi-colons.
     *
     * vCard 3.0 (rfc2425, rfc2426):
     * Parameters _may_ be surrounded by double quotes.
     * If this is not the case, semi-colon, colon and comma may simply not
     * occur (the comma used for multiple parameter values though).
     * If it is surrounded by double-quotes, it may simply not contain
     * double-quotes.
     * This means that a parameter can in no case encode double-quotes, or
     * newlines.
     *
     * vCard 4.0 (rfc6350)
     * Behavior seems to be identical to vCard 3.0
     *
     * iCalendar 2.0 (rfc5545)
     * Behavior seems to be identical to vCard 3.0
     *
     * Parameter escaping mechanism (rfc6868) :
     * This rfc describes a new way to escape parameter values.
     * New-line is encoded as ^n
     * ^ is encoded as ^^.
     * " is encoded as ^'
     *
     * @param string $input
     *
     * @return null|string
     */
    private function unescapeParam($input): string|null {

        return
            preg_replace_callback(
                '#(\^(\^|n|\'))#',
                function($matches) {
                    switch($matches[2]) {
                        case 'n' :
                            return "\n";
                        case '^' :
                            return '^';
                        case '\'' :
                            return '"';

                    // @codeCoverageIgnoreStart
                    }
                    // @codeCoverageIgnoreEnd
                },
                $input
            );
    }

    /**
     * Gets the full quoted printable value.
     *
     * We need a special method for this, because newlines have both a meaning
     * in vCards, and in QuotedPrintable.
     *
     * This method does not do any decoding.
     *
     * @return string
     */
    private function extractQuotedPrintableValue() {

        // We need to parse the raw line again to get the start of the value.
        //
        // We are basically looking for the first colon (:), but we need to
        // skip over the parameters first, as they may contain one.
        $regex = '/^
            (?: [^:])+ # Anything but a colon
            (?: "[^"]")* # A parameter in double quotes
            : # start of the value we really care about
            (.*)$
        /xs';

        preg_match($regex, $this->rawLine, $matches);

        $value = $matches[1];
        // Removing the first whitespace character from every line. Kind of
        // like unfolding, but we keep the newline.
        $value = str_replace("\n ", "\n", $value);

        // Microsoft products don't always correctly fold lines, they may be
        // missing a whitespace. So if 'forgiving' is turned on, we will take
        // those as well.
        if ($this->options & self::OPTION_FORGIVING) {
            while(substr($value,-1) === '=') {
                // Reading the line
                $this->readLine();
                // Grabbing the raw form
                $value.="\n" . $this->rawLine;
            }
        }

        return $value;

    }

}
