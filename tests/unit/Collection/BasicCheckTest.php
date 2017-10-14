<?php

namespace Collection;

use Codeception\Util\Autoload;
use Pckg\Collection;
use UnitTester;

class BasicCheckTest extends \Codeception\Test\Unit
{

    /**
     * @var UnitTester
     */
    protected $tester;

    // executed before each test
    protected function _before()
    {
        $dir = realpath(__DIR__ . '/../../../src/');

        Autoload::addNamespace('', $dir);
    }

    public function testCreation()
    {
        $collection = new Collection();
        $this->assertEquals($collection->count(), 0);

        $collection->push('foo');
        $this->assertEquals($collection->count(), 1);

        $collection->push('bar');
        $this->assertEquals($collection->count(), 2);

        $collection->pushArray(['first', 'second']);
        $this->assertEquals($collection->count(), 4);

        $collection->pop();
        $this->assertEquals(['foo', 'bar', 'first'], $collection->all());

        $collection->prepend('prepended');
        $this->assertEquals(['prepended', 'foo', 'bar', 'first'], $collection->all());

        $collection->shift();
        $this->assertEquals(['foo', 'bar', 'first'], $collection->all());

        $this->assertEquals('foo', $collection->first());
        $this->assertEquals('first', $collection->last());
    }

}