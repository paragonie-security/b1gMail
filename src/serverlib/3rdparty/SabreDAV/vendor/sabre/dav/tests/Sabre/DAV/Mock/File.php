<?php

namespace Sabre\DAV\Mock;

use Sabre\DAV;

/**
 * Mock File
 *
 * See the Collection in this directory for more details.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class File extends DAV\File {

    protected $name;
    protected $contents;
    protected $parent;

    /**
     * Creates the object
     *
     * @param string $name
     * @param array $children
     * @return void
     */
    function __construct($name, $contents, Collection $parent = null) {

        $this->name = $name;
        $this->put($contents);
        $this->parent = $parent;

    }

    /**
     * Returns the name of the node.
     *
     * This is used to generate the url.
     *
     * @return string
     */
    function getName() {

        return $this->name;

    }

    /**
     * Changes the name of the node.
     *
     * @return void
     */
    function setName($name): void {

        $this->name = $name;

    }

    /**
     * Updates the data
     *
     * The data argument is a readable stream resource.
     *
     * After a succesful put operation, you may choose to return an ETag. The
     * etag must always be surrounded by double-quotes. These quotes must
     * appear in the actual string you're returning.
     *
     * Clients may use the ETag from a PUT request to later on make sure that
     * when they update the file, the contents haven't changed in the mean
     * time.
     *
     * If you don't plan to store the file byte-by-byte, and you return a
     * different object on a subsequent GET you are strongly recommended to not
     * return an ETag, and just return null.
     *
     * @param resource $data
     * @return string|null
     */
    function put($data) {

        if (is_resource($data)) {
            $data = stream_get_contents($data);
        }
        $this->contents = $data;
        return '"' . md5($data) . '"';

    }

    /**
     * Returns the data
     *
     * This method may either return a string or a readable stream resource
     *
     * @return mixed
     */
    function get() {

        return $this->contents;

    }

    /**
     *
     * Returns the ETag for a file
     *
     * An ETag is a unique identifier representing the current version of the file. If the file changes, the ETag MUST change.
     *
     * Return null if the ETag can not effectively be determined
     */
    function getETag(): string {

        return '"' . md5($this->contents) . '"';

    }



    /**
     * Delete the node
     *
     * @return void
     */
    function delete(): void {

        $this->parent->deleteChild($this->name);

    }

}
