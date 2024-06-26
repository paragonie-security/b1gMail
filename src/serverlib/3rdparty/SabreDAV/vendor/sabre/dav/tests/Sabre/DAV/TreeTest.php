<?php

namespace Sabre\DAV;

class TreeTest extends \PHPUnit_Framework_TestCase {

    function testNodeExists(): void {

        $tree = new TreeMock();

        $this->assertTrue($tree->nodeExists('hi'));
        $this->assertFalse($tree->nodeExists('hello'));

    }

    function testCopy(): void {

        $tree = new TreeMock();
        $tree->copy('hi','hi2');

        $this->assertArrayHasKey('hi2', $tree->getNodeForPath('')->newDirectories);
        $this->assertEquals('foobar', $tree->getNodeForPath('hi/file')->get());
        $this->assertEquals(array('test1'=>'value'), $tree->getNodeForPath('hi/file')->getProperties(array()));

    }

    function testMove(): void {

        $tree = new TreeMock();
        $tree->move('hi','hi2');

        $this->assertEquals('hi2', $tree->getNodeForPath('hi')->getName());
        $this->assertTrue($tree->getNodeForPath('hi')->isRenamed);

    }

    function testDeepMove(): void {

        $tree = new TreeMock();
        $tree->move('hi/sub','hi2');

        $this->assertArrayHasKey('hi2', $tree->getNodeForPath('')->newDirectories);
        $this->assertTrue($tree->getNodeForPath('hi/sub')->isDeleted);

    }

    function testDelete(): void {

        $tree = new TreeMock();
        $tree->delete('hi');
        $this->assertTrue($tree->getNodeForPath('hi')->isDeleted);

    }

    function testGetChildren(): void {

        $tree = new TreeMock();
        $children = $tree->getChildren('');
        $this->assertEquals(2,count($children));
        $this->assertEquals('hi', $children[0]->getName());

    }

    function testGetMultipleNodes(): void {

        $tree = new TreeMock();
        $result = $tree->getMultipleNodes(['hi/sub', 'hi/file']);
        $this->assertArrayHasKey('hi/sub', $result);
        $this->assertArrayHasKey('hi/file', $result);

        $this->assertEquals('sub',  $result['hi/sub']->getName());
        $this->assertEquals('file', $result['hi/file']->getName());

    }
    function testGetMultipleNodes2(): void {

        $tree = new TreeMock();
        $result = $tree->getMultipleNodes(['multi/1', 'multi/2']);
        $this->assertArrayHasKey('multi/1', $result);
        $this->assertArrayHasKey('multi/2', $result);

    }

}

class TreeMock extends Tree {

    private $nodes = array();

    function __construct() {

        $file = new TreeFileTester('file');
        $file->properties = ['test1'=>'value'];
        $file->data = 'foobar';

        parent::__construct(
            new TreeDirectoryTester('root', [
                new TreeDirectoryTester('hi', [
                    new TreeDirectoryTester('sub'),
                    $file,
                ]),
                new TreeMultiGetTester('multi', [
                    new TreeFileTester('1'),
                    new TreeFileTester('2'),
                    new TreeFileTester('3'),
                ])
            ])
        );

    }

}

class TreeDirectoryTester extends SimpleCollection {

    public $newDirectories = array();
    public $newFiles = array();
    public $isDeleted = false;
    public $isRenamed = false;

    function createDirectory($name): void {

        $this->newDirectories[$name] = true;

    }

    function createFile($name,$data = null): void {

        $this->newFiles[$name] = $data;

    }

    function getChild($name) {

        if (isset($this->newDirectories[$name])) return new TreeDirectoryTester($name);
        if (isset($this->newFiles[$name])) return new TreeFileTester($name, $this->newFiles[$name]);
        return parent::getChild($name);

    }

    function childExists($name) {

        return !!$this->getChild($name);

    }

    function delete(): void {

        $this->isDeleted = true;

    }

    function setName($name): void {

        $this->isRenamed = true;
        $this->name = $name;

    }

}

class TreeFileTester extends File implements IProperties {

    public $name;
    public $data;
    public $properties;

    function __construct($name, $data = null) {

        $this->name = $name;
        if (is_null($data)) $data = 'bla';
        $this->data = $data;

    }

    function getName() {

        return $this->name;

    }

    function get() {

        return $this->data;

    }

    function getProperties($properties) {

        return $this->properties;

    }

    /**
     *
     * Updates properties on this node.
     *
     * This method received a PropPatch object, which contains all the
     * information about the update.
     *
     * To update specific properties, call the 'handle' method on this object.
     * Read the PropPatch documentation for more information.
     *
     * @param array $mutations
     *
     * @return void
     */
    function propPatch(PropPatch $propPatch): void {

        $this->properties = $propPatch->getMutations();
        $propPatch->setRemainingResultCode(200);

    }

}

class TreeMultiGetTester extends TreeDirectoryTester implements IMultiGet {



}
