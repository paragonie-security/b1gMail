<?php

namespace Sabre\DAVACL\FS;

class FileTest extends \PHPUnit_Framework_TestCase {

    /**
     * System under test
     *
     * @var File
     */
    protected $sut;

    protected $path = 'foo';
    protected $acl = [
        [
            'privilege' => '{DAV:}read',
            'principal' => '{DAV:}authenticated',
        ]
    ];

    protected $owner = 'principals/evert';













}
