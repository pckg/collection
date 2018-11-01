<?php namespace Pckg\Collection;

use Pckg\Collection;

/**
 * Class Each
 *
 * @package Pckg\Collection
 */
class Each
{

    /**
     * @var Collection|Each
     */
    protected $collection;

    /**
     * @var null
     */
    protected $property;

    /**
     * Each constructor.
     *
     * @param      $collection
     * @param null $property
     */
    public function __construct($collection, $property = null)
    {
        $this->collection = $collection;
        $this->property = $property;
    }

    /**
     * @param callable $callback
     *
     * @return $this|Each
     */
    public function goDeep(callable $callback)
    {
        if ($this->collection instanceof Collection) {
            return $this->collection->each($callback);
        }

        $property = $this->property;

        return $this->collection->goDeep(function($item) use ($property, $callback) {
            return $callback($item->{$property});
        });
    }

    /**
     * @param $name
     * @param $args
     *
     * @return Collection|Each
     */
    public function __call($name, $args)
    {
        $this->/*collection->*/
        goDeep(function($collection) use ($name, $args) {
            call_user_func_array([$collection, $name], $args);
            /*$collection->each(
                function($item) use ($name, $args) {
                    call_user_func_array([$item, $name], $args);
                }
            );*/
        });

        return $this->collection;
    }

    /**
     * @param $name
     *
     * @return Each
     */
    public function __get($name)
    {
        if ($name == 'each') {
            return new Each($this);
        }

        return (new Each($this, $name));
    }

}