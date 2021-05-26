<?php

namespace Collection;

use Codeception\Util\Autoload;
use Pckg\Collection;
use Pckg\Collection\Iterator;
use UnitTester;

class TransformationTest extends \Codeception\Test\Unit
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

    public function testTransformations()
    {
        $this->assertEquals('{}', (new Collection())->asObject()->jsonEncode());
        $this->assertEquals('[]', (new Collection())->asCollection()->jsonEncode());
        $this->assertEquals('[]', (new Collection())->asArray()->jsonEncode());
    }

}
