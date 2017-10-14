<?php

namespace Collection;

use Codeception\Util\Autoload;
use Pckg\Collection;
use UnitTester;

class KeysAndValuesTest extends \Codeception\Test\Unit
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
        $collection = new Collection(['foo' => 'bar', 'baz' => 'test', 'john' => 'doe', 'jane' => 'name']);
        $removedOne = $collection->removeKeys('baz');
        $this->assertEquals(['foo' => 'bar', 'john' => 'doe', 'jane' => 'name'], $removedOne->all());

        $removedMultiple = $collection->removeKeys(['baz', 'john']);
        $this->assertEquals(['foo' => 'bar', 'jane' => 'name'], $removedMultiple->all());

        $removedValues = $collection->removeValues(['bar', 'test']);
        $this->assertEquals(['john' => 'doe', 'jane' => 'name'], $removedValues->all());

        $keys = $collection->keys();
        $this->assertEquals(['foo', 'baz', 'john', 'jane'], $keys);

        $values = $collection->values();
        $this->assertEquals(['bar', 'test', 'doe', 'name'], $values);

        $this->assertEquals(true, $collection->hasKey('baz'));
        $this->assertEquals(false, $collection->hasKey('bz'));

        $getKey = $collection->getKey('baz');
        $this->assertEquals('test', $getKey);

        $this->assertEquals(true, $collection->hasKey('john'));
        $this->assertEquals(false, $collection->hasKey('johny'));
    }

}