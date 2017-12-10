<?php

namespace GeoTool\Entities;


use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Columns;
use Yaoi\Database\Definition\Index;
use Yaoi\Database\Entity;

class Story extends Entity
{
    public $id;
    public $userId;
    public $utFrom;
    public $utTo;
    public $name;
    public $title;
    public $timezone;

    /**
     * @param Columns|static $columns
     */
    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->userId = User::columns()->id;
        $columns->utTo = Column::INTEGER + Column::NOT_NULL;
        $columns->utFrom = Column::INTEGER + Column::NOT_NULL;
        $columns->name = Column::STRING + Column::NOT_NULL;
        $columns->title = Column::STRING + Column::NOT_NULL;
        $columns->timezone = Column::STRING + Column::NOT_NULL;
    }

    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        $table->addIndex(Index::TYPE_UNIQUE, $columns->userId, $columns->name);
    }

    public static function getByName($userId, $name)
    {
        $story = new Story();
        $story->name = $name;
        $story->userId = $userId;
        return $story->findSaved();
    }

}