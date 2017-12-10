<?php

namespace GeoTool\Entities;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Entity;

class Event extends Entity
{
    public $id;

    public $utStart;
    public $utEnd;
    public $totalTime;
    public $stopTime;

    public $latitudeStart;
    public $longitudeStart;
    public $latitudeEnd;
    public $longitudeEnd;
    public $distance;

    public $altitudeStart;
    public $altitudeEnd;
    public $ascending;
    public $descending;

    public $stops;
    public $minSpeed;
    public $maxSpeed;
    public $avgSpeed;
    public $avgMovingSpeed;

    /**
     * @param \stdClass|static $columns
     */
    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;

        $columns->utStart = Column::INTEGER + Column::UNSIGNED;
        $columns->utEnd = Column::INTEGER + Column::UNSIGNED;

        $columns->latitudeStart = Column::FLOAT + Column::NOT_NULL + Column::SIZE_8B;
        $columns->longitudeStart = Column::FLOAT + Column::NOT_NULL + Column::SIZE_8B;
        $columns->latitudeEnd = Column::FLOAT + Column::NOT_NULL + Column::SIZE_8B;
        $columns->longitudeEnd = Column::FLOAT + Column::NOT_NULL + Column::SIZE_8B;
        $columns->distance = Column::FLOAT + Column::NOT_NULL;

        $columns->altitudeStart = Column::FLOAT + Column::NOT_NULL;
        $columns->altitudeEnd = Column::FLOAT + Column::NOT_NULL;
        $columns->ascending = Column::FLOAT + Column::NOT_NULL;
        $columns->descending = Column::FLOAT + Column::NOT_NULL;

        $columns->avgSpeed = Column::FLOAT + Column::NOT_NULL;
        $columns->maxSpeed = Column::FLOAT + Column::NOT_NULL;
        $columns->minSpeed = Column::FLOAT + Column::NOT_NULL;
        $columns->avgMovingSpeed = Column::FLOAT + Column::NOT_NULL;
        $columns->stops = Column::INTEGER + Column::UNSIGNED + Column::SIZE_2B;

        $columns->totalTime = Column::INTEGER + Column::UNSIGNED;
        $columns->stopTime = Column::INTEGER + Column::UNSIGNED;
    }

    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {

    }

}