<?php

use Sabre\Event\EventEmitter;

include __DIR__ . '/../../vendor/autoload.php';

abstract class BenchMark {

    protected $startTime;
    protected $iterations = 10000;
    protected $totalTime;

    /**
     * @return void
     */
    function setUp() {

    }

    abstract function test();

    function go(): float {

        $this->setUp();
        $this->startTime = microtime(true);
        $this->test();
        $this->totalTime = microtime(true) - $this->startTime;
        return $this->totalTime;

    }

}

class OneCallBack extends BenchMark {

    protected $emitter;
    protected $iterations = 100000;

    function setUp(): void {

        $this->emitter = new EventEmitter();
        $this->emitter->on('foo', function() {
            // NOOP
        });

    }

    function test(): void {

        for ($i = 0;$i < $this->iterations;$i++) {
            $this->emitter->emit('foo', []);
        }

    }

}

class ManyCallBacks extends BenchMark {

    protected $emitter;

    function setUp(): void {

        $this->emitter = new EventEmitter();
        for ($i = 0;$i < 100;$i++) {
            $this->emitter->on('foo', function() {
                // NOOP
            });
        }

    }

    function test(): void {

        for ($i = 0;$i < $this->iterations;$i++) {
            $this->emitter->emit('foo', []);
        }

    }

}

class ManyPrioritizedCallBacks extends BenchMark {

    protected $emitter;

    function setUp(): void {

        $this->emitter = new EventEmitter();
        for ($i = 0;$i < 100;$i++) {
            $this->emitter->on('foo', function() {
            }, 1000 - $i);
        }

    }

    function test(): void {

        for ($i = 0;$i < $this->iterations;$i++) {
            $this->emitter->emit('foo', []);
        }

    }

}

$tests = [
    'OneCallBack',
    'ManyCallBacks',
    'ManyPrioritizedCallBacks',
];

foreach ($tests as $test) {

    $testObj = new $test();
    $result = $testObj->go();
    echo $test . " " . $result . "\n";

}
