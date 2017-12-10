<?php

namespace GeoTool\Entities;

use Yaoi\Database\Entity;

class Track extends Event
{
    public $name;

    static function setUpColumns($columns)
    {
        parent::setUpColumns($columns);
    }

    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        // TODO: Implement setUpTable() method.
    }
}