<?php

namespace Pckg;

/**
 * Interface CollectionInterface
 *
 * @package Pckg
 */
interface CollectionInterface
{
    public function push($item, $key = null, $forceKey = false);
}
