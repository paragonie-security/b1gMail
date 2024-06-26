<?php

namespace Sabre\DAV\PropertyStorage\Backend;

use Sabre\DAV\PropFind;
use Sabre\DAV\PropPatch;

class Mock implements BackendInterface {

    public $data = [];

    /**
     * Fetches properties for a path.
     *
     * This method received a PropFind object, which contains all the
     * information about the properties that need to be fetched.
     *
     * Ususually you would just want to call 'get404Properties' on this object,
     * as this will give you the _exact_ list of properties that need to be
     * fetched, and haven't yet.
     *
     * @param string $path
     * @param PropFind $propFind
     * @return void
     */
    public function propFind($path, PropFind $propFind): void {

        if (!isset($this->data[$path])) {
            return;
        }

        foreach($this->data[$path] as $name=>$value) {
            $propFind->set($name, $value);
        }

    }

    /**
     * Updates properties for a path
     *
     * This method received a PropPatch object, which contains all the
     * information about the update.
     *
     * Usually you would want to call 'handleRemaining' on this object, to get;
     * a list of all properties that need to be stored.
     *
     * @param string $path
     * @param PropPatch $propPatch
     * @return void
     */
    public function propPatch($path, PropPatch $propPatch): void {

        if (!isset($this->data[$path])) {
            $this->data[$path] = [];
        }
        $propPatch->handleRemaining(function($properties) use ($path) {

            foreach($properties as $propName=>$propValue) {

                if (is_null($propValue)) {
                    unset($this->data[$path][$propName]);
                } else {
                    $this->data[$path][$propName] = $propValue;
                }
                return true;

            }

        });

    }

    /**
     * This method is called after a node is deleted.
     *
     * This allows a backend to clean up all associated properties.
     */
    public function delete($path): void {

        unset($this->data[$path]);

    }

    /**
     * This method is called after a successful MOVE
     *
     * This should be used to migrate all properties from one path to another.
     * Note that entire collections may be moved, so ensure that all properties
     * for children are also moved along.
     *
     * @param string $source
     * @param string $destination
     * @return void
     */
    public function move($source, $destination): void {

        foreach($this->data as $path => $props) {

            if ($path === $source) {
                $this->data[$destination] = $props;
                unset($this->data[$path]);
                continue;
            }

            if (strpos($path, $source . '/')===0) {
                $this->data[$destination . substr($path, strlen($source)+1)] = $props;
                unset($this->data[$path]);
            }

        }

    }

}
