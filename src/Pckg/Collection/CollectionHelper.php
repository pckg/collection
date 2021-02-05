<?php

namespace Pckg\Collection;

trait CollectionHelper
{

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->collection[] = $value;
        } else {
            $this->collection[$offset] = $value;
        }

        if (method_exists($this, 'markDirty')) {
            $this->markDirty();
        }
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->collection[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->collection[$offset]);

        if (method_exists($this, 'markDirty')) {
            $this->markDirty();
        }
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     * @throws Exception
     */
    public function offsetGet($offset)
    {
        if (!array_key_exists($offset, $this->collection)) {
            return null;
        }

        return $this->collection[$offset];
    }

    /**
     * @return mixed
     */
    public function rewind()
    {
        return reset($this->collection);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->collection);
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return key($this->collection);
    }

    /**
     * @return mixed
     */
    public function next()
    {
        return next($this->collection);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return key($this->collection) !== null;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return $this->collection;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->collection);
    }
}
