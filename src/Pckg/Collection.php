<?php

namespace Pckg;

use ArrayAccess;
use Countable;
use Exception;
use JsonSerializable;
use Pckg\Collection\CollectionHelper;
use Pckg\Collection\Each;
use Pckg\Collection\Iterator;
use Pckg\Collection\Tryout;
use Pckg\Database\Obj;
use Pckg\Database\Record;
use Throwable;

/**
 * Class Collection
 *
 * @package Pckg\Database
 *
 * @property object|mixed $each
 */
class Collection extends Iterator implements ArrayAccess, JsonSerializable, Countable, CollectionInterface
{
    protected $total;

    protected $object = false;

    /**
     * @return Collection|Each
     * @throws Exception
     */
    public function __get($name)
    {
        if ($name == 'each') {
            return $this->each();
        } else if ($name == 'try') {
            return $this->try();
        }

        throw new Exception('Calling ' . $name . ' on Collection');
    }

    /**
     * @return Collection
     */
    public function createCollection($collection = []): Collection
    {
        return new Collection($collection);
    }

    /**
     * @return $this
     */
    public function asObject(bool $asObject = true)
    {
        $this->object = $asObject;

        return $this;
    }

    /**
     * @return $this
     */
    public function asArray(bool $asArray = true)
    {
        $this->object = !$asArray;

        return $this;
    }

    /**
     * @param bool $asCollection
     * @return $this
     * @deprecated
     */
    public function asCollection(bool $asCollection = true)
    {
        $this->object = !$asCollection;

        return $this;
    }

    /**
     * Remove items with speciffic keys.
     */
    public function removeKeys($keys)
    {
        $collection = $this->createCollection();
        if (!is_array($keys)) {
            $keys = [$keys];
        }

        foreach ($this->collection as $key => $item) {
            if (in_array($key, $keys)) {
                continue;
            }

            $collection->push($item, $key);
        }

        return $collection;
    }

    /**
     * @param array $data
     *
     * @return mixed|Collection
     */
    public static function create($data = [])
    {
        $class = static::class;

        return new $class($data);
    }

    /**
     * @return mixed|Collection
     */
    public function rekey()
    {
        return static::create(array_values($this->collection));
    }

    /**
     * Remove items with speciffic values.
     */
    public function removeValues($values, $strict = false)
    {
        $collection = $this->createCollection();

        foreach ($this->collection as $key => $item) {
            if (in_array($item, $values, $strict)) {
                continue;
            }

            $collection->push($item, $key);
        }

        return $collection;
    }

    /**
     * Remove $value from collection.
     */
    public function removeValue($value)
    {
        $collection = $this->createCollection();

        foreach ($this->collection as $key => $item) {
            if ($item == $value) {
                continue;
            }

            $collection->push($item, $key);
        }

        return $collection;
    }

    /**
     * Add element to end of collection array.
     */
    public function push($item, $key = null, $forceKey = false)
    {
        if ($key || ($key === 0) || $forceKey) {
            $this->collection[$key] = $item;
        } else {
            $this->collection[] = $item;
        }

        return $this;
    }

    /**
     * Add element to specified group.
     */
    public function pushToGroup($item, $group, $key = null)
    {
        if ($key || $key === 0) {
            $this->collection[$group][$key] = $item;
        } else {
            $this->collection[$group][] = $item;
        }

        return $this;
    }

    /**
     * @return $this
     * Push multiple items to end of collection array.
     */
    public function pushArray($items)
    {
        foreach ($items as $item) {
            $this->push($item);
        }

        return $this;
    }

    /**
     * Remove element from end of array.
     */
    public function pop()
    {
        if (!$this->collection) {
            return null;
        }

        return array_pop($this->collection);
    }

    /**
     * Add element to beginning of array.
     */
    public function prepend($item, $key = null)
    {
        if (
            $key || $key === 0 ||
            ($this->collection && array_keys($this->collection) != range(0, $this->count() - 1))
        ) {
            $collection = $this->createCollection([$key => $item]);
            foreach ($this->collection as $k => $i) {
                if ($k && is_string($key) && $k === $key) {
                    continue; // do not overwrite item
                }
                $collection->push($i, $k);
            }
            $this->collection = $collection->all();
        } else {
            array_unshift($this->collection, $item);
        }

        return $this;
    }

    /**
     * Remove element from beginning of array.
     */
    public function shift()
    {
        if (!$this->collection) {
            return null;
        }

        return array_shift($this->collection);
    }

    /**
     * @return Collection
     * See php implementation of slice method.
     */

    public function slice($offset, ?int $length = null, bool $preserve_keys = false)
    {
        return $this->createCollection(array_slice($this->collection, $offset, $length, $preserve_keys));
    }

    /**
     * @return array
     * Return collection keys.
     */
    public function keys()
    {
        return array_keys($this->collection);
    }

    /**
     * @return array
     * Return collection values.
     */
    public function values()
    {
        return array_values($this->collection);
    }

    /**
     * @return int
     * Return count / total number of items in collection.
     */
    public function total()
    {
        return $this->total ? $this->total : $this->count();
    }

    /**
     * @return mixed
     * Returns item's property, array's key value or calls and returns callback, based on input.
     */
    protected function getValueOrCallable($item, $param, $i)
    {
        return is_only_callable($param) ? $param($item, $i) : (is_object($item) ? $item->{$param} : $item[$param]);
    }

    /**
     * @return float|mixed
     * Sums values of data.
     */
    public function sum($callable = null)
    {
        $sum = 0.0;
        foreach ($this->collection as $i => $item) {
            $partial = $callable ? $this->getValueOrCallable($item, $callable, $i) : $item;
            if ($partial > 0 || $partial < 0) {
                $sum += $partial;
            }
        }

        return $sum;
    }

    /**
     * @return float
     * Returns average value of collection
     */
    public function avg($callable = null)
    {
        if (!$this->collection) {
            return 0;
        }

        return $this->sum($callable) / $this->count();
    }

    /**
     * Chunks collection into multiple parts.
     */
    public function chunk($by)
    {
        $chunks = [];
        $index = 0;
        foreach ($this->collection as $item) {
            if (!array_key_exists($index, $chunks)) {
                $chunks[$index] = [];
            }

            $chunks[$index][] = $item;

            if (count($chunks[$index]) == $by) {
                $index++;
            }
        }

        return $this->createCollection($chunks);
    }

    /**
     * @return $this
     * Randomizes collection.
     */
    public function shuffle()
    {
        shuffle($this->collection);

        return $this;
    }

    /**
     * @return bool
     * Check if collection is holding speciffic item.
     */
    public function has($condition = null)
    {
        if (!$condition) {
            return $this->count() > 0;
        }

        if (is_only_callable($condition)) {
            foreach ($this->collection as $i => $item) {
                if ($condition($item, $i)) {
                    return true;
                }
            }

            return false;
        }

        return in_array($condition, $this->collection);
    }

    /**
     * @return $this
     * Set number of total items for partial collections.
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Copy items to new collection.
     */
    public function copy()
    {
        return $this->createCollection($this->collection);
    }

    /**
     * @param CollectionInterface $collection
     * Copy items from collection to another collection.
     */
    public function copyTo(CollectionInterface $collection, $preserveKeys = false)
    {
        $this->each(function ($item, $i) use ($collection, $preserveKeys) {
            $collection->push($item, $preserveKeys ? $i : null, $preserveKeys);
        });

        return $collection;
    }

    /**
     * @param callable $callback
     * @param bool $preserveKeys
     *
     * @return Collection
     * Filters collection by condition.
     */
    public function reduce(callable $callback, $preserveKeys = false)
    {
        $collection = $this->createCollection();

        foreach ($this->collection as $key => $item) {
            if ($callback($item)) {
                $collection->push($item, $preserveKeys ? $key : null);
            }
        }

        return $collection;
    }

    public function realReduce(callable $callback, $start)
    {
        foreach ($this->collection as $key => $item) {
            $start = $callback($item, $key, $start, $this);
        }

        return $start;
    }

    /**
     * @return mixed
     * @throws Exception
     * Returns value based on input.
     */
    protected function getValue($object, $key, $index = null)
    {
        if (is_only_callable($key)) {
            return $key($object, $index);
        } elseif (is_object($object) && method_exists($object, $key)) {
            return $object->{$key}();
        } elseif (is_object($object)) {
            return $object->{$key};
        } elseif (is_array($object) && array_key_exists($key, $object)) {
            return $object[$key];
        }

        throw new Exception("Cannot find key $key ");
    }

    /**
     * @return mixed
     * Get item by key.
     */
    public function getKey($key, $default = null)
    {
        return array_key_exists($key, $this->collection) ? $this->collection[$key] : $default;
    }

    /**
     * @return bool
     * Check if key exists in collection.
     */
    public function hasKey($key)
    {
        return array_key_exists($key, $this->collection);
    }

    /**
     * @return Collection
     * @deprecated
     */
    public function getTree($foreign, $primary = 'id')
    {
        $tree = new Collection\Tree($this->collection);

        return $this->createCollection($tree->getHierarchy($foreign, $primary));
    }

    /**
     * @param string $key
     *
     * Build array tree from items.
     */
    public function tree($foreign = 'parent_id', $primary = 'id', $key = 'getChildren')
    {
        $children = [];
        $parents = [];
        $items = [];
        if (is_array($this->first())) {
            foreach ($this->collection as &$item) {
                $parentId = $this->getValue($item, $foreign);
                $primaryId = $this->getValue($item, $primary);
                $items[$primaryId] = &$item;
                if ($parentId) {
                    $children[$parentId][] = &$item;
                } else {
                    $parents[] = &$item;
                }
            }

            foreach ($items as $primaryId => &$item) {
                $item[$key] = &$children[$primaryId] ?? [];
            }
        } else {
            foreach ($this->collection as $item) {
                $parentId = $this->getValue($item, $foreign);
                $items[$this->getValue($item, $primary)] = $item;
                if ($parentId) {
                    $children[$parentId][] = $item;
                } else {
                    $parents[] = $item;
                }
            }

            foreach ($items as $primaryId => $item) {
                $item->{$key} = $children[$primaryId] ?? [];
            }
        }

        return $this->createCollection($parents);
    }

    /**
     * @return Collection
     * Sort items by condition.
     */
    public function sortBy($sortBy = null, $sort_flags = null)
    {
        if (!$sortBy) {
            return $this->sort($sort_flags);
        }

        $arrSort = [];
        foreach ($this->groupAndSort($sortBy, $sort_flags) as $group) {
            foreach ($group as $row) {
                $arrSort[] = $row;
            }
        }

        return $this->createCollection($arrSort);
    }

    /**
     * @param int $sort_flags
     */
    public function sort($sort_flags = SORT_REGULAR)
    {
        $arrSort = $this->collection;
        sort($arrSort, $sort_flags);

        return $this->createCollection($arrSort);
    }

    /**
     * @param int $sort_flags
     */
    public function ksort($sort_flags = SORT_REGULAR)
    {
        $arrSort = $this->collection;
        ksort($arrSort, $sort_flags);

        return new static($arrSort);
    }

    /**
     * @param int $sort_flags
     */
    public function asort($sort_flags = SORT_REGULAR)
    {
        $arrSort = $this->collection;
        asort($arrSort, $sort_flags);

        return new static($arrSort);
    }

    /**
     * @return array
     * Group items and sort them by condition.
     */
    protected function groupAndSort($sortBy, $sort_flags = null)
    {
        $arr = [];

        foreach ($this->collection as $i => $row) {
            $arr[$this->getValueOrCallable($row, $sortBy, $i)][] = $row;
        }

        ksort($arr, $sort_flags);

        return $arr;
    }

    /**
     * @return null
     * Return random element from collection.
     */
    public function random()
    {
        if (!$this->collection) {
            return null;
        }

        return $this->collection[array_rand($this->collection)];
    }

    public function reverse()
    {
        return $this->createCollection(array_reverse($this->collection));
    }

    /**
     * @return Collection
     * Group items by input.
     */
    public function groupBy($groupBy)
    {
        $arrGroupped = [];

        foreach ($this->collection as $i => $row) {
            if (is_only_callable($groupBy)) {
                $arrGroupped[$groupBy($row, $i)][] = $row;
            } else {
                $arrGroupped[$this->getValue($row, $groupBy)][] = $row;
            }
        }

        return $this->createCollection($arrGroupped);
    }

    /**
     * @param string $comparator
     *
     * @return Collection
     * Filter collection by filter condition.
     */
    public function filter($filterBy, $value = true, $comparator = '==')
    {
        $collection = $this->createCollection();

        foreach ($this->collection as $i => $row) {
            if (is_only_callable($filterBy)) {
                if ($filterBy($row, $i)) {
                    $collection->push($row, $i);
                }
            } else {
                $objectValue = $this->getValue($row, $filterBy);

                if (
                    (($comparator == '==') &&
                        ((is_array($value) && in_array($objectValue, $value)) || ($objectValue == $value)) ||
                        (($comparator == '===') && ($objectValue === $value)) ||
                        (($comparator == '<=') && ($objectValue <= $value)) ||
                        (($comparator == '>=') && ($objectValue >= $value)) ||
                        (($comparator == '!=') && ($objectValue != $value)) ||
                        (($comparator == '!==') && ($objectValue !== $value)))
                ) {
                    $collection->push($row, $i);
                }
            }
        }

        return $collection;
    }

    public function keyByValue()
    {

        $collection = $this->createCollection();
        foreach ($this->collection as $item) {
            $collection->push($item, $item);
        }

        return $collection;
    }

    /**
     * Key collection by key.
     */
    public function keyBy($key)
    {
        $collection = $this->createCollection();
        foreach ($this->collection as $i => $item) {
            $collection->push(
                $item,
                is_only_callable($key) ? $key($item, $i) : (is_object($item) ? $item->{$key} : $item[$key])
            );
        }

        return $collection;
    }

    /**
     * @return $this|Collection
     * Remove empty elements from collection.
     */
    public function removeEmpty($preserveKeys = false)
    {
        $collection = $this->createCollection();
        foreach ($this->collection as $key => $item) {
            if (!$item) {
                continue;
            }

            $collection->push($item, $preserveKeys ? $key : null);
        }

        return $collection;
    }

    /**
     * @return null|mixed|Record
     * Return first item that meets condition.
     */
    public function first(callable $filter = null, $returnKey = false)
    {
        if (!$this->collection) {
            return null;
        }

        if (!$filter) {
            $keys = array_keys($this->collection);
            if ($returnKey) {
                return $keys[0];
            }
            return $this->collection[$keys[0]];
        }

        foreach ($this->collection as $i => $item) {
            if ($filter($item, $i)) {
                return $returnKey ? $i : $item;
            }
        }

        return null;
    }

    /**
     * @return null|mixed
     * Return last item.
     */
    public function last()
    {
        return $this->collection ? $this->collection[array_reverse(array_keys($this->collection))[0]] : null;
    }

    /**
     * @return array
     * Return original collection.
     */
    public function all()
    {
        if ($this->collection) {
            return $this->collection;
        }

        return [];
    }

    /**
     * @param callable|null $callback
     *
     * @return $this|Collection|Each|Collection\Each
     * Call $callback on each item in collection.
     */
    public function each(callable $callback = null)
    {
        if (!$callback) {
            return new Each($this);
        }

        foreach ($this->collection as $i => $item) {
            $callback($item, $i, $this);
        }

        return $this;
    }

    /**
     * @return $this|Tryout
     */
    public function try(&$e = [], callable $callback = null)
    {
        return (new Tryout($this))->setE($e)->setExceptionCallback($callback);
    }

    /**
     * Manually create new collection.
     */
    public function eachManual($callback)
    {
        $collection = $this->createCollection();
        foreach ($this->collection as $i => $item) {
            $callback($item, $i, $collection);
        }

        return $collection;
    }

    /**
     * Flatten 2d collection.
     */
    public function flat($key = null)
    {
        $collection = $this->createCollection();

        $current = $key ? $this->map($key) : $this;
        $current->each(function ($item) use ($collection) {
            collect($item)->each(function ($item) use ($collection) {
                $collection->push($item);
            });
        });

        return $collection;
    }

    /**
     * @return Collection
     * Trim each item in collection.
     */
    public function trim()
    {
        return $this->map(function ($item) {
            return trim($item);
        });
    }

    /**
     * @return array
     */
    private function privateMap($item, $mapper)
    {
        $data = [];
        foreach ($mapper as $f => $k) {
            /**
             * Map partial relation
             */
            if (is_array($k)) {
                $data[$f] = $this->privateMap($item->{$f}, $k);
                continue;
            }
            /**
             * Map full relation.
             */
            if ($k === '*') {
                $data[$f] = $item->toArray();
                continue;
            }
            /**
             * Map field.
             * @warning: changed in May 2021! Was: $data[$k] = ...
             */
            $data[is_int($f) ? $k : $f] = $this->getValue($item, $k);
        }

        return $data;
    }

    /**
     * @param callable $callable
     * @return mixed
     */
    public function pass(callable $callable)
    {
        return $callable($this);
    }

    /**
     * Map collection items.
     */
    public function map($field)
    {
        $collection = $this->createCollection();

        if (is_bool($field)) {
            foreach ($this->collection as $i => $item) {
                $collection->push($field, $i);
            }
        } else if (is_array($field)) {
            foreach ($this->collection as $i => $item) {
                $collection->push($this->privateMap($item, $field), $i);
            }
        } else {
            foreach ($this->collection as $i => $item) {
                $newItem = is_only_callable($field)
                    ? $field($item, $i)
                    : (is_object($item) ? $item->{$field}
                        : $item[$field]);
                $collection->push($newItem, $i);
            }
        }

        return $collection;
    }

    /**
     * @param string $fn
     * @param mixed ...$args
     */
    public function mapFn(string $fn, ...$args)
    {
        if (!function_exists($fn)) {
            throw new Exception('Function ' . $fn . ' does not exist.');
        }

        return $this->map(function ($value) use ($fn, $args) {
            return $fn($value, ...$args);
        });
    }

    /**
     * @param string $fn
     * @param mixed ...$args
     */
    public function mapFnObject(string $fn, ...$args)
    {
        return $this->map(function ($value) use ($fn, $args) {
            return $value->{$fn}(...$args);
        });
    }

    /**
     * Filter fields in collection items.
     */
    public function only($keys)
    {
        $collection = $this->createCollection();

        $this->each(function ($item, $key) use ($keys, $collection) {
            $collection->push(only($item, $keys), $key);
        });

        return $collection;
    }

    /**
     * @return array
     * Transform collection items by rules.
     */
    public function transform($rules)
    {
        return $this->map($rules)->all();
    }

    /**
     * Make items unique.
     */
    public function unique()
    {
        return $this->createCollection(array_unique($this->collection));
    }

    /**
     * @return mixed|null|Record
     * Return minimum value.
     */
    public function min($field = null)
    {
        if (!$this->collection) {
            return null;
        }

        $collection = $this;
        if ($field) {
            $collection = $this->map($field);
        }

        if ($collection->count() == 1) {
            return $collection->first();
        }

        return min(...$collection->all());
    }

    /**
     * @return mixed|null|Record
     * Return maximum value.
     */
    public function max($field = null)
    {
        if (!$this->collection) {
            return null;
        }

        $collection = $this;
        if ($field) {
            $collection = $this->map($field);
        }

        if ($collection->count() == 1) {
            return $collection->first();
        }

        return max(...$collection->all());
    }

    /**
     * @param string $separator
     *
     * @return null|string
     * Implode collection items.
     */
    public function implode($separator = '', $lastSeparator = null)
    {
        if (!$this->collection) {
            return null;
        } elseif ($this->count() === 1) {
            return (string)$this->first();
        } elseif ($lastSeparator) {
            return implode($separator, $this->slice(0, $this->count() - 1)->all()) . $lastSeparator .
                (string)$this->last();
        }

        return implode($separator, $this->collection);
    }

    /**
     * @return $this|Collection
     * Multiply collection items.
     */
    public function multiply($count)
    {
        if ($count < 0) {
            return $this;
        }

        $items = [];

        for ($i = 0; $i < $count; $i++) {
            foreach ($this->collection as $item) {
                $items[] = $item;
            }
        }

        return new Collection($items);
    }

    /**
     * @return array
     */
    public function toArray($values = null, $depth = 6)
    {
        return $this->__toArray($values, $depth);
    }

    /**
     * @param int $depth
     *
     * @return string
     */
    public function toJSON($depth = 6)
    {
        try {
            $json = json_encode(
                (array)$this->__toArray(null, $depth),
                JSON_OBJECT_AS_ARRAY | JSON_NUMERIC_CHECK | JSON_PARTIAL_OUTPUT_ON_ERROR
            );
        } catch (Throwable $e) {
        }

        return $json ?? '[]';
    }

    /**
     * @return array
     */
    public function __toArray($values = null, $depth = 6)
    {
        $return = [];

        if (!$depth) {
            // @phpstan-ignore-next-line
            return;
        }

        if (!$values) {
            $values = $this->collection;
        }

        if (is_string($values) || is_numeric($values)) {
            // @phpstan-ignore-next-line
            return $values;
        } elseif ($values instanceof Record) {
            return $values->__toArray(null, $depth - 1);
        } elseif ($values instanceof Obj) {
            return $this->__toArray($values->data(), $depth - 1);
        } elseif (is_array($values) || object_implements($values, CollectionInterface::class)) {
            foreach ($values as $key => $value) {
                if (is_object($value)) {
                    $return[$key] = $this->__toArray($value, $depth - 1);
                } elseif (is_array($value)) {
                    $return[$key] = $value ? $this->__toArray($value, $depth - 1) : $value;
                } else {
                    $return[$key] = $value;
                }
            }
        } else if (object_implements($values, JsonSerializable::class)) {
            return $values->jsonSerialize();
        }

        return $return;
    }

    /**
     * @return array|Exception
     */
    public function jsonSerialize(): mixed
    {
        try {
            $serialize = $this->__toArray();
        } catch (Throwable $e) {
            error_log('Exception in json serialize: ' . exception($e));
            // @phpstan-ignore-next-line
            return null;
        }

        if (!$serialize) {
            return $this->object
                ? new \stdClass()
                : [];
        }

        return $serialize;
    }

    /**
     * @return false|string
     */
    public function jsonEncode()
    {
        $empty = $this->object ? '{}' : '[]';
        $flags = $this->object ? JSON_FORCE_OBJECT : JSON_OBJECT_AS_ARRAY;

        $encoded = json_encode($this->jsonSerialize(), $flags);

        if ($encoded) {
            return $encoded;
        }

        return $empty;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->jsonEncode();
    }
}
