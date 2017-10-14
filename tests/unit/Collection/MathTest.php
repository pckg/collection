<?php

namespace Collection;

use Codeception\Util\Autoload;
use Pckg\Collection;
use Pckg\Stringify;
use UnitTester;

class MathTest extends \Codeception\Test\Unit
{

    /**
     * @var UnitTester
     */
    protected $tester;

    // executed before each test
    protected function _before()
    {
        $dir = realpath(__DIR__ . '/../../../src');

        Autoload::addNamespace('', $dir);
        require_once $dir . '/Pckg/Collection/Helper/functions.php';
    }

    public function testCreation()
    {
        $collection = new Collection([2, 1, 13, 3, 1, 5, 21, 8]);
        $sum = $collection->sum();
        $this->assertEquals(54, $sum);

        $avg = $collection->avg();
        $this->assertEquals(6.75, $avg);

        $min = $collection->min();
        $this->assertEquals(1, $min);

        $max = $collection->max();
        $this->assertEquals(21, $max);
    }

}