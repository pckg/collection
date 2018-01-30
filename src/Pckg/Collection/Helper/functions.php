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
    function collect($data, $of = Collection::class)
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

if (!function_exists('is_only_callable')) {
    function is_only_callable($input)
    {
        if (is_string($input)) {
            return false;
        }

        return is_callable($input);
    }
}
