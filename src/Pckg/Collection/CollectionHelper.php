<?php

namespace Pckg\Collection;

trait CollectionHelper
{
    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
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
    public function offsetExists($offset): bool
    {
        return isset($this->collection[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
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
     */
    public function offsetGet($offset): mixed
    {
        if (!array_key_exists($offset, $this->collection)) {
            return null;
        }

        return $this->collection[$offset];
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        reset($this->collection);
    }

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function current(): mixed
    {
        return current($this->collection);
    }

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function key(): mixed
    {
        return key($this->collection);
    }

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function next(): mixed
    {
        return next($this->collection);
    }

    /**
     * @return bool
     */
    public function valid(): bool
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
    public function count(): int
    {
        return count($this->collection);
    }
}
