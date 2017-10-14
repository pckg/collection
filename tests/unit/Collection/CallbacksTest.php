<?php

namespace Collection;

use Codeception\Util\Autoload;
use Pckg\Collection;
use UnitTester;

class CallbacksTest extends \Codeception\Test\Unit
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
        $collection = new Collection([
                                         'foo' => [
                                             'id'    => 1,
                                             'title' => 'baz',
                                         ],
                                         'bar' => [
                                             'id'    => 2,
                                             'title' => 'unknown',
                                         ],
                                     ]);

        $this->assertEquals(true, $collection->has(['id' => 1, 'title' => 'baz']));
        $this->assertEquals(false, $collection->has(['id' => 2, 'title' => 'baz']));

        $filtered = $collection->filter(function($item) {
            return $item['id'] == 1;
        });
        $this->assertEquals(['foo' => ['id' => 1, 'title' => 'baz']], $filtered->all());

        $keyed = $collection->keyBy('title');
        $this->assertEquals([
                                'baz'     => [
                                    'id'    => 1,
                                    'title' => 'baz',
                                ],
                                'unknown' => [
                                    'id'    => 2,
                                    'title' => 'unknown',
                                ],
                            ], $keyed->all());

        $first = $collection->first(function($item) { return $item['id'] > 1; });
        $this->assertEquals(['id' => 2, 'title' => 'unknown'], $first);

        $mapped = $collection->map('title');
        $this->assertEquals(['foo' => 'baz', 'bar' => 'unknown'], $mapped->all());
    }

}