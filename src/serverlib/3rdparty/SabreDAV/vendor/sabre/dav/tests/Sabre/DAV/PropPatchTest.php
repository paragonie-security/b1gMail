<?php

namespace Sabre\DAV;

class PropPatchTest extends \PHPUnit_Framework_TestCase {

    protected $propPatch;

    public function setUp(): void {

        $this->propPatch = new PropPatch([
            '{DAV:}displayname' => 'foo',
        ]);
        $this->assertEquals(['{DAV:}displayname' => 'foo'], $this->propPatch->getMutations());

    }

    public function testHandleSingleSuccess(): void {

        $hasRan = false;

        $this->propPatch->handle('{DAV:}displayname', function($value) use (&$hasRan) {
            $hasRan = true;
            $this->assertEquals('foo', $value);
            return true;
        });

        $this->assertTrue($this->propPatch->commit());
        $result = $this->propPatch->getResult();
        $this->assertEquals(['{DAV:}displayname' => 200], $result);

        $this->assertTrue($hasRan);

    }

    public function testHandleSingleFail(): void {

        $hasRan = false;

        $this->propPatch->handle('{DAV:}displayname', function($value) use (&$hasRan) {
            $hasRan = true;
            $this->assertEquals('foo', $value);
            return false;
        });

        $this->assertFalse($this->propPatch->commit());
        $result = $this->propPatch->getResult();
        $this->assertEquals(['{DAV:}displayname' => 403], $result);

        $this->assertTrue($hasRan);

    }

    public function testHandleSingleCustomResult(): void {

        $hasRan = false;

        $this->propPatch->handle('{DAV:}displayname', function($value) use (&$hasRan) {
            $hasRan = true;
            $this->assertEquals('foo', $value);
            return 201;
        });

        $this->assertTrue($this->propPatch->commit());
        $result = $this->propPatch->getResult();
        $this->assertEquals(['{DAV:}displayname' => 201], $result);

        $this->assertTrue($hasRan);

    }

    public function testHandleSingleDeleteSuccess(): void {

        $hasRan = false;

        $this->propPatch = new PropPatch(['{DAV:}displayname' => null]);
        $this->propPatch->handle('{DAV:}displayname', function($value) use (&$hasRan) {
            $hasRan = true;
            $this->assertNull($value);
            return true;
        });

        $this->assertTrue($this->propPatch->commit());
        $result = $this->propPatch->getResult();
        $this->assertEquals(['{DAV:}displayname' => 204], $result);

        $this->assertTrue($hasRan);

    }


    public function testHandleNothing(): void {

        $hasRan = false;

        $this->propPatch->handle('{DAV:}foobar', function($value) use (&$hasRan) {
            $hasRan = true;
        });

        $this->assertFalse($hasRan);

    }

    /**
     * @depends testHandleSingleSuccess
     */
    public function testHandleRemaining(): void {

        $hasRan = false;

        $this->propPatch->handleRemaining(function($mutations) use (&$hasRan) {
            $hasRan = true;
            $this->assertEquals(['{DAV:}displayname' => 'foo'], $mutations);
            return true;
        });

        $this->assertTrue($this->propPatch->commit());
        $result = $this->propPatch->getResult();
        $this->assertEquals(['{DAV:}displayname' => 200], $result);

        $this->assertTrue($hasRan);

    }
    public function testHandleRemainingNothingToDo(): void {

        $hasRan = false;

        $this->propPatch->handle('{DAV:}displayname', function() {} );
        $this->propPatch->handleRemaining(function($mutations) use (&$hasRan) {
            $hasRan = true;
        });

        $this->assertFalse($hasRan);

    }

    public function testSetResultCode(): void {

        $this->propPatch->setResultCode('{DAV:}displayname', 201);
        $this->assertTrue($this->propPatch->commit());
        $result = $this->propPatch->getResult();
        $this->assertEquals(['{DAV:}displayname' => 201], $result);

    }

    public function testSetResultCodeFail(): void {

        $this->propPatch->setResultCode('{DAV:}displayname', 402);
        $this->assertFalse($this->propPatch->commit());
        $result = $this->propPatch->getResult();
        $this->assertEquals(['{DAV:}displayname' => 402], $result);

    }

    public function testSetRemainingResultCode(): void {

        $this->propPatch->setRemainingResultCode(204);
        $this->assertTrue($this->propPatch->commit());
        $result = $this->propPatch->getResult();
        $this->assertEquals(['{DAV:}displayname' => 204], $result);

    }

    public function testCommitNoHandler(): void {

        $this->assertFalse($this->propPatch->commit());
        $result = $this->propPatch->getResult();
        $this->assertEquals(['{DAV:}displayname' => 403], $result);

    }

    public function testHandlerNotCalled(): void {

        $hasRan = false;

        $this->propPatch->setResultCode('{DAV:}displayname', 402);
        $this->propPatch->handle('{DAV:}displayname', function($value) use (&$hasRan) {
            $hasRan = true;
        });

        $this->propPatch->commit();

        // The handler is not supposed to have ran
        $this->assertFalse($hasRan);

    }

    public function testDependencyFail(): void {

        $propPatch = new PropPatch([
            '{DAV:}a' => 'foo',
            '{DAV:}b' => 'bar',
        ]);

        $calledA = false;
        $calledB = false;

        $propPatch->handle('{DAV:}a', function() use (&$calledA) {
            $calledA = true;
            return false;
        });
        $propPatch->handle('{DAV:}b', function() use (&$calledB) {
            $calledB = true;
            return false;
        });

        $result = $propPatch->commit();
        $this->assertTrue($calledA);
        $this->assertFalse($calledB);

        $this->assertFalse($result);

        $this->assertEquals([
            '{DAV:}a' => 403,
            '{DAV:}b' => 424,
        ], $propPatch->getResult());

    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testHandleSingleBrokenResult(): void {

        $propPatch = new PropPatch([
            '{DAV:}a' => 'foo',
        ]);

        $calledA = false;
        $calledB = false;

        $propPatch->handle('{DAV:}a', function() use (&$calledA) {
            return [];
        });
        $propPatch->commit();

    }

    public function testHandleMultiValueSuccess(): void {

        $propPatch = new PropPatch([
            '{DAV:}a' => 'foo',
            '{DAV:}b' => 'bar',
            '{DAV:}c' => null,
        ]);

        $calledA = false;

        $propPatch->handle(['{DAV:}a', '{DAV:}b', '{DAV:}c'], function($properties) use (&$calledA) {
            $calledA = true;
            $this->assertEquals([
                '{DAV:}a' => 'foo',
                '{DAV:}b' => 'bar',
                '{DAV:}c' => null,
            ], $properties);
            return true;
        });
        $result = $propPatch->commit();
        $this->assertTrue($calledA);
        $this->assertTrue($result);

        $this->assertEquals([
            '{DAV:}a' => 200,
            '{DAV:}b' => 200,
            '{DAV:}c' => 204,
        ], $propPatch->getResult());

    }


    public function testHandleMultiValueFail(): void {

        $propPatch = new PropPatch([
            '{DAV:}a' => 'foo',
            '{DAV:}b' => 'bar',
            '{DAV:}c' => null,
        ]);

        $calledA = false;

        $propPatch->handle(['{DAV:}a', '{DAV:}b', '{DAV:}c'], function($properties) use (&$calledA) {
            $calledA = true;
            $this->assertEquals([
                '{DAV:}a' => 'foo',
                '{DAV:}b' => 'bar',
                '{DAV:}c' => null,
            ], $properties);
            return false;
        });
        $result = $propPatch->commit();
        $this->assertTrue($calledA);
        $this->assertFalse($result);

        $this->assertEquals([
            '{DAV:}a' => 403,
            '{DAV:}b' => 403,
            '{DAV:}c' => 403,
        ], $propPatch->getResult());

    }

    public function testHandleMultiValueCustomResult(): void {

        $propPatch = new PropPatch([
            '{DAV:}a' => 'foo',
            '{DAV:}b' => 'bar',
            '{DAV:}c' => null,
        ]);

        $calledA = false;

        $propPatch->handle(['{DAV:}a', '{DAV:}b', '{DAV:}c'], function($properties) use (&$calledA) {
            $calledA = true;
            $this->assertEquals([
                '{DAV:}a' => 'foo',
                '{DAV:}b' => 'bar',
                '{DAV:}c' => null,
            ], $properties);

            return [
                '{DAV:}a' => 201,
                '{DAV:}b' => 204,
            ];
            return false;
        });
        $result = $propPatch->commit();
        $this->assertTrue($calledA);
        $this->assertFalse($result);

        $this->assertEquals([
            '{DAV:}a' => 201,
            '{DAV:}b' => 204,
            '{DAV:}c' => 500,
        ], $propPatch->getResult());

    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testHandleMultiValueBroken(): void {

        $propPatch = new PropPatch([
            '{DAV:}a' => 'foo',
            '{DAV:}b' => 'bar',
            '{DAV:}c' => null,
        ]);

        $calledA = false;

        $propPatch->handle(['{DAV:}a', '{DAV:}b', '{DAV:}c'], function($properties) use (&$calledA) {
            return 'hi';
        });
        $propPatch->commit();

    }
}
