<?php

namespace Pckg\Collection;

use Pckg\Collection;

/**
 * Class Tree
 *
 * @package Pckg\Collection
 */
class Tree extends Collection
{

    /**
     * @var
     */
    protected $foreign;

    protected $primary;

    /* sets callback to retreive relation/key */

    /**
     * @param $foreign
     *
     * @return array
     */
    public function getHierarchy($foreign, $primary = 'id')
    {
        $this->setForeign($foreign);
        $this->setPrimary($primary);

        $parents = $this->getParents();

        foreach ($parents AS &$parent) {
            $parent = $this->buildParent($parent);
        }

        return $parents;
    }

    /* builds tree */

    /**
     * @param $foreign
     */
    public function setForeign($foreign)
    {
        $this->foreign = $foreign;
    }

    public function setPrimary($primary)
    {
        $this->primary = $primary;
    }

    /* transforms parent into object/array and children */

    /**
     * @return array
     */
    public function getParents()
    {
        $arrParents = [];

        foreach ($this->collection AS $row) {
            $foreignValue = is_callable($this->foreign)
                ? ($this->foreign)($row)
                : $row->{$this->foreign};

            if (!$foreignValue) { // has no set parent
                $arrParents[] = $row;
                continue;
            }

            $found = false;
            foreach ($this->collection AS $row2) { // if has no parent
                $primaryValue = is_callable($this->primary)
                    ? ($this->primary)($row2)
                    : $row2->id;
                if ($foreignValue == $primaryValue) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $arrParents[] = $row;
            }
        }

        return $arrParents;
    }

    /* recursively builds parents */

    /**
     * @param $parent
     *
     * @return mixed
     */
    public function buildParent($parent)
    {
        $parent->getChildren = $this->buildChildren($parent);

        //$parent->subcontents = $parent->getChildren;

        return $parent;
    }

    /* returns records with $this->foreign != true */

    /**
     * @param null $parent
     *
     * @return array
     */
    public function buildChildren($parent = null)
    {
        $arrChildren = $this->getChildren($parent);

        foreach ($arrChildren AS &$child) {
            $child = $this->buildParent($child);
        }

        return $arrChildren;
    }

    /* returns records with $this->foreign != false */

    /**
     * @param null $parent
     *
     * @return array
     */
    public function getChildren($parent = null)
    {
        $arrChildren = [];

        if ($parent) {
            $primaryValue = is_callable($this->primary)
                ? ($this->primary)($parent)
                : ($parent->id);
            foreach ($this->collection AS $one) {
                $foreignValue = is_callable($this->foreign)
                    ? ($this->foreign)($one)
                    : $one->{$this->foreign};
                if ($primaryValue == $foreignValue) {
                    $arrChildren[] = $one;
                }
            }
        }

        return $arrChildren;
    }
}