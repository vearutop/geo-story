<?php

namespace GeoTool\Entities;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Entity;

class Photo extends Entity
{
    public $id;
    public $storyId;
    public $ut;
    public $latitude;
    public $longitude;
    public $urlName;

    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->storyId = Story::columns()->id;
        $columns->ut = Column::INTEGER + Column::UNSIGNED;
        $columns->latitude = Column::FLOAT + Column::NOT_NULL + Column::SIZE_8B;
        $columns->longitude = Column::FLOAT + Column::NOT_NULL + Column::SIZE_8B;
        $columns->urlName = Column::STRING;
    }

    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
    }


}