<?php

namespace Pckg\Collection;

use Pckg\Collection;

/**
 * Class Tryout
 *
 * @package Pckg\Collection
 */
class Tryout extends Each
{
    protected $e = [];

    protected $tryMulti = false;

    protected $exceptionCallback = null;

    /**
     * @return Collection|Each
     */
    public function __call($name, $args)
    {
        try {
            return $this->collection->{$name}(...$args);
        } catch (\Throwable $e) {
            $this->collectException($e);
        }

        return $this->collection;
    }

    /**
     * @return Each
     */
    public function __get($name)
    {
        try {
            return $this->collection->{$name};
            // @phpstan-ignore-next-line
        } catch (\Throwable $e) {
            $this->collectException($e);
        }

        // @phpstan-ignore-next-line
        return new Each($this);
    }

    public function collectException(\Throwable $e)
    {
        $this->e[] = $e;
        if ($callback = $this->exceptionCallback) {
            $callback($e);
        }

        return $this;
    }

    public function setE(&$e = [])
    {
        $this->e = $e;

        return $this;
    }

    public function setExceptionCallback(callable $callback = null)
    {
        $this->exceptionCallback = $callback;

        return $this;
    }
}
