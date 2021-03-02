<?php

namespace Collection;

use Codeception\Util\Autoload;
use Pckg\Collection;
use UnitTester;

class ManipulationTest extends \Codeception\Test\Unit
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
        require_once $dir . '/Pckg/Collection/Helper/functions.php';
    }

    public function testCreation()
    {
        $collection = new Collection(['foo', 'bar', 'baz', '', ' untrimmed ']);

        $sliced = $collection->slice(1, 2);
        $this->assertEquals(['bar', 'baz'], $sliced->all());

        $chunked = $collection->chunk(2);
        $this->assertEquals([['foo', 'bar'], ['baz', ''], [' untrimmed ']], $chunked->all());

        $flatten = $chunked->flat();
        $this->assertEquals($collection->all(), $flatten->all());

        $trimmed = $collection->trim();
        $this->assertEquals(['foo', 'bar', 'baz', '', 'untrimmed'], $trimmed->all());

        $multiplied = $collection->multiply(2);
        $this->assertEquals([
                                'foo',
                                'bar',
                                'baz',
                                '',
                                ' untrimmed ',
                                'foo',
                                'bar',
                                'baz',
                                '',
                                ' untrimmed ',
                            ], $multiplied->all());

        $unique = $multiplied->unique();
        $this->assertEquals($unique->all(), $collection->all());

        $imploded = $collection->implode(' ', ' - ');
        $this->assertEquals('foo bar baz  -  untrimmed ', $imploded);

        $nonEmpty = $collection->removeEmpty();
        $this->assertEquals(['foo', 'bar', 'baz', ' untrimmed '], $nonEmpty->all());

        $nonEmpty = $collection->removeEmpty(true);
        $this->assertEquals(['foo', 'bar', 'baz', 4 => ' untrimmed '], $nonEmpty->all());

        $reduced = $collection->filter(function($item) {
            return strlen($item) == 3;
        });
        $this->assertEquals(['foo', 'bar', 'baz'], $reduced->all());
    }

}
