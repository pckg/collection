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
        $this->collection = $array ?? [];
    }

}