<?php

use Pckg\Collection;
use Pckg\Stringify;

if (!function_exists('collect')) {
    /**
     * @return Collection|mixed
     */
    function collect($data = [], $of = Collection::class)
    {
        return new $of($data);
    }
}

if (!function_exists('stringify')) {
    /**
     * @return Stringify
     */
    function stringify($string)
    {
        return new Stringify($string);
    }
}
