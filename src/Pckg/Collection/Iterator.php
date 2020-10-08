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
        if (is_object($array) && $array instanceof Collection) {
            $array = $array->all();
        } else if (is_object($array)) {
            /**
             * Objects can be passed, but they MUST implement __toArray();
             */
            $array = $array->__toArray();
        }

        $this->collection = $array ?? [];
    }

}