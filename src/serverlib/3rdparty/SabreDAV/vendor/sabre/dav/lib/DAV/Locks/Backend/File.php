<?php

namespace Sabre\DAV\Locks\Backend;

use Sabre\DAV\Locks\LockInfo;

/**
 * This Locks backend stores all locking information in a single file.
 *
 * Note that this is not nearly as robust as a database. If you are considering
 * using this backend, keep in mind that the PDO backend can work with SqLite,
 * which is designed to be a good file-based database.
 *
 * It literally solves the problem this class solves as well, but much better.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class File extends AbstractBackend {

    /**
     * The storage file
     *
     * @var string
     */
    private $locksFile;

    /**
     * Constructor
     *
     * @param string $locksFile path to file
     */
    function __construct($locksFile) {

        $this->locksFile = $locksFile;

    }

    /**
     * Returns a list of Sabre\DAV\Locks\LockInfo objects
     *
     * This method should return all the locks for a particular uri, including
     * locks that might be set on a parent uri.
     *
     * If returnChildLocks is set to true, this method should also look for
     * any locks in the subtree of the uri for locks.
     *
     * @param string $uri
     * @param bool $returnChildLocks
     * @return array
     */
    function getLocks($uri, $returnChildLocks) {

        $newLocks = [];

        $locks = $this->getData();

        foreach ($locks as $lock) {

            if ($lock->uri === $uri ||
                //deep locks on parents
                ($lock->depth != 0 && strpos($uri, $lock->uri . '/') === 0) ||

                // locks on children
                ($returnChildLocks && (strpos($lock->uri, $uri . '/') === 0))) {

                $newLocks[] = $lock;

            }

        }

        // Checking if we can remove any of these locks
        foreach ($newLocks as $k => $lock) {
            if (time() > $lock->timeout + $lock->created) unset($newLocks[$k]);
        }
        return $newLocks;

    }





    /**
     * Loads the lockdata from the filesystem.
     *
     * @return array
     */
    protected function getData() {

        if (!file_exists($this->locksFile)) return [];

        // opening up the file, and creating a shared lock
        $handle = fopen($this->locksFile, 'r');
        flock($handle, LOCK_SH);

        // Reading data until the eof
        $data = stream_get_contents($handle);

        // We're all good
        flock($handle, LOCK_UN);
        fclose($handle);

        // Unserializing and checking if the resource file contains data for this file
        $data = unserialize($data);
        if (!$data) return [];
        return $data;

    }



}
