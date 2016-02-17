<?php

namespace GeoTool\Reader\GPSEssentials;


use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Entity;

class Track extends Entity
{
    public $id;
    public $routeId;
    public $starred;
    public $description;
    public $name;

    static function setUpColumns($columns)
    {
        $columns->id = Column::create(Column::AUTO_ID);
        $columns->id->schemaName = '_id';

        $columns->routeId = Column::INTEGER;
        $columns->starred = Column::INTEGER;
        $columns->description = Column::STRING;
        $columns->name = Column::STRING;
    }

    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        $table->setSchemaName('Track');
    }


}