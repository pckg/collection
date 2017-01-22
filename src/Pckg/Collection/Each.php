<?php namespace Pckg\Collection;

use Pckg\Collection;

class Each
{

    /**
     * @var Collection|Each
     */
    protected $collection;

    protected $property;

    public function __construct($collection, $property = null)
    {
        $this->collection = $collection;
        $this->property = $property;
    }

    public function goDeep(callable $callback)
    {
        if ($this->collection instanceof Collection) {
            return $this->collection->each($callback);
        }

        $property = $this->property;

        return $this->collection->goDeep(
            function($item) use ($property, $callback) {
                return $callback($item->{$property});
            }
        );
    }

    public function __call($name, $args)
    {
        $this->collection->goDeep(
            function($collection) use ($name, $args) {
                $collection->each(
                    function($item) use ($name, $args) {
                        call_user_func_array([$item, $name], $args);
                    }
                );
            }
        );

        return $this->collection;
    }

    public function __get($name)
    {
        if ($name == 'each') {
            return new Each($this);
        } else {
            return (new Each($this, $name));
        }
    }

}