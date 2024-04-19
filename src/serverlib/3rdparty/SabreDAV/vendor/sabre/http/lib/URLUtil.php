<?php

namespace Sabre\HTTP;

use Sabre\URI;

/**
 * URL utility class
 *
 * Note: this class is deprecated. All its functionality moved to functions.php
 * or sabre\uri.
 *
 * @deprectated
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class URLUtil {

    /**
     * Encodes the path of a url.
     *
     * slashes (/) are treated as path-separators.
     *
     * @deprecated use \Sabre\HTTP\encodePath()
     * @param string $path
     * @return string
     */
    static function encodePath($path) {

        return encodePath($path);

    }



    /**
     * Decodes a url-encoded path
     *
     * @deprecated use \Sabre\HTTP\decodePath
     * @param string $path
     * @return string
     */
    static function decodePath($path) {

        return decodePath($path);

    }



    /**
     * Returns the 'dirname' and 'basename' for a path.
     *
     * @deprecated Use Sabre\Uri\split().
     * @param string $path
     * @return array
     */
    static function splitPath($path) {

        return Uri\split($path);

    }



}
