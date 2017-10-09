<?php namespace Pckg;

class Stringify
{

    protected $value;

    public function __construct(string $value = null)
    {
        $this->value = $value;
    }

    public function sha1()
    {
        return new Stringify(sha1($this->value));
    }

    public function explode($separator)
    {
        return explode($separator, $this->value);
    }

    public function explodeToCollection($separator)
    {
        return new Collection($this->explode($separator));
    }

    public function __toString()
    {
        return (string)$this->value;
    }

}