<?php namespace Pckg\Collection;

use Pckg\Collection;

class Each
{

    /**
     * @var Collection
     */
    protected $collection;

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function __call($name, $args)
    {
        $this->collection->each(
            function($item) use ($name, $args) {
                call_user_func_array([$item, $name], $args);
            }
        );

        return $this->collection;
    }

}