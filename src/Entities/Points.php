<?php

namespace GeoTool\Entities;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Entity;

class Points extends Entity
{
    public $id;
    public $ut;
    public $altitude;
    public $longitude;
    public $latitude;
    public $accuracy;

    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->ut = Column::INTEGER;
        $columns->altitude = Column::FLOAT;
        $columns->longitude = Column::FLOAT;
        $columns->latitude = Column::FLOAT;
        $columns->accuracy = Column::FLOAT;
    }

    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        // TODO: Implement setUpTable() method.
    }


}