<?php

use Pckg\Collection;
use Pckg\Stringify;

if (!function_exists('collect')) {
    /**
     * @param $data
     * @param $of
     *
     * @return Collection
     */
    function collect($data = [], $of = Collection::class)
    {
        return new $of($data);
    }
}

if (!function_exists('stringify')) {
    /**
     * @param $string
     *
     * @return Stringify
     */
    function stringify($string)
    {
        return new Stringify($string);
    }
}
