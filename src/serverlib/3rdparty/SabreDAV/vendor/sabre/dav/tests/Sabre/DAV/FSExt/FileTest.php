<?php

namespace Sabre\DAV\FSExt;

use Sabre\DAV;

require_once 'Sabre/TestUtil.php';

class FileTest extends \PHPUnit_Framework_TestCase {

    function setUp(): void {

        file_put_contents(SABRE_TEMPDIR . '/file.txt', 'Contents');

    }

    function tearDown(): void {

        \Sabre\TestUtil::clearTempDir();

    }

    function testPut(): void {

        $filename = SABRE_TEMPDIR . '/file.txt';
        $file = new File($filename);
        $result = $file->put('New contents');

        $this->assertEquals('New contents',file_get_contents(SABRE_TEMPDIR . '/file.txt'));
        $this->assertEquals(
            '"' .
            sha1(
                fileinode($filename) .
                filesize($filename ) .
                filemtime($filename)
            ) . '"',
            $result
        );

    }

    function testRange(): void {

        $file = new File(SABRE_TEMPDIR . '/file.txt');
        $file->put('0000000');
        $file->patch('111', 2, 3);

        $this->assertEquals('0001110',file_get_contents(SABRE_TEMPDIR . '/file.txt'));

    }

    function testRangeStream(): void {

        $stream = fopen('php://memory','r+');
        fwrite($stream, "222");
        rewind($stream);

        $file = new File(SABRE_TEMPDIR . '/file.txt');
        $file->put('0000000');
        $file->patch($stream, 2, 3);

        $this->assertEquals('0002220',file_get_contents(SABRE_TEMPDIR . '/file.txt'));

    }


    function testGet(): void {

        $file = new File(SABRE_TEMPDIR . '/file.txt');
        $this->assertEquals('Contents',stream_get_contents($file->get()));

    }

    function testDelete(): void {

        $file = new File(SABRE_TEMPDIR . '/file.txt');
        $file->delete();

        $this->assertFalse(file_exists(SABRE_TEMPDIR . '/file.txt'));

    }

    function testGetETag(): void {

        $filename = SABRE_TEMPDIR . '/file.txt';
        $file = new File($filename);
        $this->assertEquals(
            '"' .
            sha1(
                fileinode($filename) .
                filesize($filename ) .
                filemtime($filename)
            ) . '"',
            $file->getETag()
        );
    }

    function testGetContentType(): void {

        $file = new File(SABRE_TEMPDIR . '/file.txt');
        $this->assertNull($file->getContentType());

    }

    function testGetSize(): void {

        $file = new File(SABRE_TEMPDIR . '/file.txt');
        $this->assertEquals(8,$file->getSize());

    }

}
