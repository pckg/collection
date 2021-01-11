<?php namespace Pckg\Collection;

use Pckg\Collection;

/**
 * Class Iterator
 *
 * @package Pckg\Collection
 */
class Iterator extends \EmptyIterator
{

    use CollectionHelper;

    /**
     * @var array
     */
    protected $collection = [];

    /**
     * @param array $array
     */
    public function __construct($array = [])
    {
        if (is_object($array)) {
            if ($array instanceof Collection) {
                $array = $array->all();
            } else if (method_exists($array, 'toArray')) {
                /**
                 * Objects can be passed, but they MUST implement __toArray();
                 */
                $array = $array->toArray();
            } else if (method_exists($array, '__toArray')) {
                /**
                 * Objects can be passed, but they MUST implement __toArray();
                 */
                $array = $array->__toArray();
            } else {
                throw new Exception('Object must implement toArray or __toArray to be collected');
            }
        }

        $this->collection = $array ?? [];
    }

}