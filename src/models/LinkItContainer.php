<?php

namespace simpleteam\linkit\models;


use craft\base\Model;

class LinkItContainer extends Model
{
    public $links = array();
    public function addLink(LinkItModel $link) {
        $this->links[] = $link;
    }
}