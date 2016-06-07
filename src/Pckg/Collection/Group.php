<?php

namespace Pckg\Collection;

use Pckg\Collection;

/**
 * Class Group
 * @package Pckg\Collection
 */
class Group extends Collection
{
    /**
     * @var
     */
    protected $groupBy;

    /* builds groups */
    /**
     * @param $groupBy
     * @return array
     * @throws \Exception
     */
    public function getGroupped($groupBy)
    {
        $arrGroupped = [];

        foreach ($this->collection AS $row) {
            if (is_callable($groupBy)) {
                $arrGroupped[$groupBy($row)][] = $row;

            } else {
                $arrGroupped[$this->getValue($row, $this->groupBy)][] = $row;
            }
        }

        return $arrGroupped;
    }
}