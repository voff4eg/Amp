<?php

use Amp\ThreadedDispatcher,
    Alert\NativeReactor;

class ThreadedDispatcherTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider provideBadOptionKeys
     * @requires extension pthreads
     * @expectedException \DomainException
     */
    function testSetOptionThrowsOnUnknownOption($badOptionName) {
        $reactor = new NativeReactor;
        $dispatcher = new ThreadedDispatcher($reactor);
        $dispatcher->setOption($badOptionName, 42);
    }

    function provideBadOptionKeys() {
        return [
            ['unknownName'],
            ['someothername']
        ];
    }

    /**
     * @requires extension pthreads
     */
    function testNativeFunctionDispatch() {
        $reactor = new NativeReactor;
        $dispatcher = new ThreadedDispatcher($reactor);
        $dispatcher->start(1);
        $dispatcher->call('strlen', 'zanzibar!', function($result) use ($reactor) {
            $this->assertEquals($result->getResult(), 9);
            $reactor->stop();
        });
        $reactor->run();
    }

    /**
     * @requires extension pthreads
     */
    function testUserlandFunctionDispatch() {
        $reactor = new NativeReactor;
        $dispatcher = new ThreadedDispatcher($reactor);
        $dispatcher->start(1);
        $dispatcher->call('multiply', 6, 7, function($result) use ($reactor) {
            $this->assertEquals($result->getResult(), 42);
            $reactor->stop();
        });
        $reactor->run();
    }

    /**
     * @expectedException \Amp\DispatchException
     * @requires extension pthreads
     */
    function testErrorResultReturnedIfInvocationThrows() {
        $reactor = new NativeReactor;
        $dispatcher = new ThreadedDispatcher($reactor);
        $dispatcher->start(1);
        $dispatcher->call('exception', function($result) use ($reactor) {
            $this->assertTrue($result->failed());
            $result->getResult();
            $reactor->stop();
        });
        $reactor->run();
    }

}