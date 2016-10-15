<?php

namespace Pckg;

use ArrayAccess;
use Exception;
use JsonSerializable;
use Pckg\Collection\Iterator;
use Pckg\Database\Record;

/**
 * Class Collection
 *
 * @package Pckg\Database
 */
class Collection extends Iterator implements ArrayAccess, JsonSerializable, CollectionInterface
{

    protected $total;

    public function push($item, $key = null)
    {
        if ($key) {
            $this->collection[$key] = $item;

        } else {
            $this->collection[] = $item;

        }

        return $this;
    }

    public function slice($offset, $length = null, $preserve_keys = null)
    {
        return new Collection(array_slice($this->collection, $offset, $length, $preserve_keys));
    }

    public function getKeys()
    {
        return array_keys($this->collection);
    }

    public function total()
    {
        return $this->total ? $this->total : count($this->collection);
    }

    public function sum(callable $callable)
    {
        $sum = 0.0;

        foreach ($this->collection as $item) {
            $partial = $callable($item);
            if ($partial > 0 || $partial < 0) {
                $sum += $partial;
            }
        }

        return $sum;
    }

    public function has(callable $callable)
    {
        foreach ($this->collection as $item) {
            if ($callable($item)) {
                return true;
            }
        }

        return false;
    }

    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    public function copy()
    {
        return new static($this->collection);
    }

    public function copyTo(CollectionInterface $collection)
    {
        $this->each(
            function($item) use ($collection) {
                $collection->push($item);
            }
        );
    }

    public function reduce(callable $callback, $preserveKeys = false)
    {
        $collection = new self();

        foreach ($this->collection as $key => $item) {
            if ($callback($item)) {
                $collection->push($item, $preserveKeys ? $key : null);
            }
        }

        return $collection;
    }

    /* helper */
    /**
     * @param $object
     * @param $key
     *
     * @return mixed
     * @throws Exception
     */
    protected function getValue($object, $key)
    {
        if (is_object($object) && method_exists($object, $key)) {
            return $object->{$key}();

        } else if (is_object($object) && isset($object->{$key})) {
            return $object->{$key};

        } else if (is_array($object) && array_key_exists($key, $object)) {
            return $object[$key];

        }

        throw new Exception("Cannot find key $key in object " . get_class($object));
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getKey($key)
    {
        return $this->collection[$key];
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function keyExists($key)
    {
        return array_key_exists($key, $this->collection);
    }

    /* strategies */
    /**
     * @return Collection
     */
    public function getIdAsKey()
    {
        $list = new Collection\Lista($this->collection);

        return new Collection($list->getIdAsKey());
    }

    /**
     * @return Collection
     */
    public function getList()
    {
        $list = new Collection\Lista($this->collection);

        return new Collection($list->getList());
    }

    /**
     * @return Collection
     */
    public function getListID()
    {
        $list = new Collection\Lista($this->collection);

        return new Collection($list->getListID());
    }

    /**
     * @param $callback
     *
     * @return Collection
     */
    public function getCustomList($callback)
    {
        $list = new Collection\Lista($this->collection);

        return new Collection($list->getCustomList($callback));
    }

    /**
     * @param $foreign
     *
     * @return Collection
     */
    public function getTree($foreign)
    {
        $tree = new Collection\Tree($this->collection);

        return new Collection($tree->getHierarchy($foreign));
    }

    /**
     * @param $sortBy
     *
     * @return Collection
     */
    public function sortBy($sortBy)
    {
        $sort = new Collection\Sort($this->collection);

        return new Collection($sort->getSorted($sortBy));
    }

    /**
     * @param $groupBy
     *
     * @return Collection
     */
    public function groupBy($groupBy)
    {
        $group = new Collection\Group($this->collection);

        return new Collection($group->getGroupped($groupBy));
    }

    /**
     * @param        $filterBy
     * @param        $value
     * @param string $comparator
     *
     * @return Collection
     */
    public function filter($filterBy, $value, $comparator = '==')
    {
        $filter = new Collection\Filter($this->collection);

        return new Collection($filter->getFiltered($filterBy, $value, $comparator));
    }

    /**
     * @param     $limitCount
     * @param int $limitOffset
     *
     * @return Collection
     */
    public function limit($limitCount, $limitOffset = 0)
    {
        $limit = new Collection\Limit($this->collection);

        return new Collection($limit->getLimited($limitCount, $limitOffset));
    }

    public function keyBy($key)
    {
        $collection = new Collection();
        foreach ($this->collection as $item) {
            $collection->push(
                $item,
                is_callable($key)
                    ? $key($item)
                    : (
                is_object($item)
                    ? $item->{$key}
                    : $item[$key]
                )
            );
        }

        return $collection;
    }

    public function removeEmpty()
    {
        foreach ($this->collection as $key => $item) {
            if (!$item) {
                unset($this->collection[$key]);
            }
        }

        return $this;
    }

    /**
     * @return null
     */
    public function first(callable $filter = null)
    {
        if (!$this->collection) {
            return null;
        }

        if (!$filter) {
            return $this->collection[array_keys($this->collection)[0]];
        }

        foreach ($this->collection as $item) {
            if ($filter($item)) {
                return $item;
            }
        }

        return null;
    }

    public function last()
    {
        return $this->collection
            ? $this->collection[array_reverse(array_keys($this->collection))[0]]
            : null;
    }

    public function all()
    {
        return $this->collection;
    }

    public function each($callback, $new = true, $preserveKey = true)
    {
        if ($new) {
            $class = static::class;
            $collection = new $class;
            foreach ($this->collection as $i => $item) {
                $collection->push($callback($item), $preserveKey ? $i : null);
            }

            return $collection;
        } else {
            foreach ($this->collection as $item) {
                $callback($item);
            }

            return $this;
        }
    }

    public function map($field)
    {
        return new static(
            array_map(
                function($item) use ($field) {
                    return is_callable($field)
                        ? $field($item)
                        : $item->{$field};
                },
                $this->collection
            )
        );
    }

    public function unique()
    {
        return new static(array_unique($this->collection));
    }

    public function implode($separator)
    {
        return implode($separator, $this->collection);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->collection[] = $value;
        } else {
            $this->collection[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->collection[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->collection[$offset]);
    }

    public function offsetGet($offset)
    {
        if (!array_key_exists($offset, $this->collection)) {
            throw new Exception(
                'Key ' . $offset . ' doesn\'t exist in collection ' . implode(',', array_keys($this->collection))
            );
        }

        return $this->collection[$offset];
    }

    public function toArray()
    {
        return $this->__toArray();
    }

    public function toJSON($depth = 6)
    {
        return json_encode((array)$this->__toArray(null, $depth), JSON_OBJECT_AS_ARRAY | JSON_NUMERIC_CHECK);
    }

    /**
     * @return array
     */
    public function __toArray($values = null, $depth = 6)
    {
        $return = [];

        if (!$depth) {
            return;
        }

        if (!$values) {
            $values = $this->collection;
        }

        if (is_array($values) || object_implements($values, CollectionInterface::class)) {
            foreach ($values as $key => $value) {
                /*if (is_object($value) && object_implements($object, RecordInterface::class)) {
                    $return[$key] = $object->toArray($object, $depth - 1);

                } else */
                if (is_object($value)) {
                    $return[$key] = $this->__toArray($value, $depth - 1);

                } else if (is_array($value)) {
                    $return[$key] = $this->__toArray($value, $depth - 1);

                } else {
                    $return[$key] = $value;

                }
            }
        } elseif ($values instanceof Record) {
            $return = $values->__toArray(null, $depth - 1);
        }

        return $return;
    }

    public function jsonSerialize()
    {
        try {
            $serialize = $this->__toArray();
        } catch (Exception $e) {
            return exception($e);
        }

        return $serialize;
    }

    public function __toString()
    {
        return json_encode($this->jsonSerialize());
    }

}