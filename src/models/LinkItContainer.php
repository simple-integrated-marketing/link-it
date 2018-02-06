<?php

namespace simpleteam\linkit\models;


use craft\base\Model;

class LinkItContainer extends Model implements \IteratorAggregate
{
    public $links = array();
    public function addLink(LinkItModel $link) {
        $this->links[] = $link;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->links);
    }
}