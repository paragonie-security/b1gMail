<?php

namespace Sabre\Event;

class PromiseTest extends \PHPUnit_Framework_TestCase {

    function testSuccess(): void {

        $finalValue = 0;
        $promise = new Promise();
        $promise->fulfill(1);

        $promise->then(function($value) use (&$finalValue) {
            $finalValue = $value + 2;
        });

        $this->assertEquals(3, $finalValue);

    }

    function testFail(): void {

        $finalValue = 0;
        $promise = new Promise();
        $promise->reject(1);

        $promise->then(null, function($value) use (&$finalValue) {
            $finalValue = $value + 2;
        });

        $this->assertEquals(3, $finalValue);

    }

    function testChain(): void {

        $finalValue = 0;
        $promise = new Promise();
        $promise->fulfill(1);

        $promise->then(function($value) use (&$finalValue) {
            $finalValue = $value + 2;
            return $finalValue;
        })->then(function($value) use (&$finalValue) {
            $finalValue = $value + 4;
            return $finalValue;
        });

        $this->assertEquals(7, $finalValue);

    }
    function testChainPromise(): void {

        $finalValue = 0;
        $promise = new Promise();
        $promise->fulfill(1);

        $subPromise = new Promise();

        $promise->then(function($value) use ($subPromise) {
            return $subPromise;
        })->then(function($value) use (&$finalValue) {
            $finalValue = $value + 4;
            return $finalValue;
        });

        $subPromise->fulfill(2);

        $this->assertEquals(6, $finalValue);

    }

    function testPendingResult(): void {

        $finalValue = 0;
        $promise = new Promise();

        $promise->then(function($value) use (&$finalValue) {
            $finalValue = $value + 2;
        });

        $promise->fulfill(4);
        $this->assertEquals(6, $finalValue);

    }

    function testPendingFail(): void {

        $finalValue = 0;
        $promise = new Promise();

        $promise->then(null, function($value) use (&$finalValue) {
            $finalValue = $value + 2;
        });

        $promise->reject(4);
        $this->assertEquals(6, $finalValue);

    }

    function testExecutorSuccess(): void {

        $promise = (new Promise(function($success, $fail) {

            $success('hi');

        }))->then(function($result) use (&$realResult) {

            $realResult = $result;

        });

        $this->assertEquals('hi', $realResult);

    }

    function testExecutorFail(): void {

        $promise = (new Promise(function($success, $fail) {

            $fail('hi');

        }))->then(function($result) use (&$realResult) {

            $realResult = 'incorrect';

        }, function($reason) use (&$realResult) {

            $realResult = $reason;

        });

        $this->assertEquals('hi', $realResult);

    }

    /**
     * @expectedException \Sabre\Event\PromiseAlreadyResolvedException
     */
    function testFulfillTwice(): void {

        $promise = new Promise();
        $promise->fulfill(1);
        $promise->fulfill(1);

    }

    /**
     * @expectedException \Sabre\Event\PromiseAlreadyResolvedException
     */
    function testRejectTwice(): void {

        $promise = new Promise();
        $promise->reject(1);
        $promise->reject(1);

    }

    function testFromFailureHandler(): void {

        $ok = 0;
        $promise = new Promise();
        $promise->error(function($reason) {

            $this->assertEquals('foo', $reason);
            throw new \Exception('hi');

        })->then(function() use (&$ok) {

            $ok = -1;

        }, function() use (&$ok) {

            $ok = 1;

        });

        $this->assertEquals(0, $ok);
        $promise->reject('foo');
        $this->assertEquals(1, $ok);

    }

    function testAll(): void {

        $promise1 = new Promise();
        $promise2 = new Promise();

        $finalValue = 0;
        Promise::all([$promise1, $promise2])->then(function($value) use (&$finalValue) {

            $finalValue = $value;

        });

        $promise1->fulfill(1);
        $this->assertEquals(0, $finalValue);
        $promise2->fulfill(2);
        $this->assertEquals([1, 2], $finalValue);

    }

    function testAllReject(): void {

        $promise1 = new Promise();
        $promise2 = new Promise();

        $finalValue = 0;
        Promise::all([$promise1, $promise2])->then(
            function($value) use (&$finalValue) {
                $finalValue = 'foo';
                return 'test';
            },
            function($value) use (&$finalValue) {
                $finalValue = $value;
            }
        );

        $promise1->reject(1);
        $this->assertEquals(1, $finalValue);
        $promise2->reject(2);
        $this->assertEquals(1, $finalValue);

    }

}
