<?php namespace Pckg\Collection;

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
        /**
         * Objects can be passed, but they MUST implement __toArray();
         */
        if (is_object($array)) {
            $array = $array->__toArray();
        }

        $this->collection = $array ?? [];
    }

}