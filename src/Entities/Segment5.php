<?php

namespace GeoTool\Entities;


use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Index;
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
        $columns->latitude = Column::FLOAT + Column::NOT_NULL;
        $columns->longitude = Column::FLOAT + Column::NOT_NULL;
        $columns->altitude = Column::FLOAT + Column::NOT_NULL;
        $columns->distance = Column::FLOAT + Column::NOT_NULL;
        $columns->elevation = Column::FLOAT + Column::NOT_NULL;
        $columns->speed = Column::FLOAT + Column::NOT_NULL;
        $columns->time = Column::INTEGER + Column::UNSIGNED;
    }

    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        $table->addIndex(Index::TYPE_KEY, $columns->ut);
    }


}