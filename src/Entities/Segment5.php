<?php

namespace GeoTool\Entities;


use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Entity;

class Segment5 extends Entity
{
    public $id;
    public $ut;
    public $latitude;
    public $longitude;
    public $altitude;
    public $distance;
    public $elevation;
    public $speed;
    public $time;

    const MIN_DISTANCE = 5;

    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->ut = Column::INTEGER + Column::UNSIGNED;
        $columns->latitude = Column::FLOAT;
        $columns->longitude = Column::FLOAT;
        $columns->altitude = Column::FLOAT;
        $columns->distance = Column::FLOAT;
        $columns->elevation = Column::FLOAT;
        $columns->speed = Column::FLOAT;
        $columns->time = Column::INTEGER + Column::UNSIGNED;
    }

    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        // TODO: Implement setUpTable() method.
    }


}