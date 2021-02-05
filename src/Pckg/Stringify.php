<?php

namespace Pckg;

/**
 * Class Stringify
 *
 * @package Pckg
 */
class Stringify
{

    /**
     * @var string
     */
    protected $value;

    /**
     * Stringify constructor.
     *
     * @param string|null $value
     */
    public function __construct(string $value = null)
    {
        $this->value = $value;
    }

    /**
     * @return Stringify
     */
    public function sha1()
    {
        return new Stringify(sha1($this->value));
    }

    /**
     * @param $separator
     *
     * @return array
     */
    public function explode($separator)
    {
        return explode($separator, $this->value);
    }

    /**
     * @param $separator
     *
     * @return Collection
     */
    public function explodeToCollection($separator)
    {
        return new Collection($this->explode($separator));
    }

    /**
     * @return mixed
     */
    public function jsonDecode($assoc = true, $options = JSON_PARTIAL_OUTPUT_ON_ERROR)
    {
        return json_decode($this->value, true, 512, $options);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }
}
