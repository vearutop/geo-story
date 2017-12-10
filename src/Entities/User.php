<?php

namespace GeoTool\Entities;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Columns;
use Yaoi\Database\Definition\Index;
use Yaoi\Database\Entity;

class User extends Entity
{
    public $id;
    public $login;
    public $name;

    /**
     * @param Columns|static $columns
     */
    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->login = Column::STRING + Column::NOT_NULL;
        $columns->name = Column::STRING + Column::NOT_NULL;
    }

    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        $table->addIndex(Index::TYPE_UNIQUE, $columns->login);
    }

    /**
     * @param $login
     * @return static
     */
    static function getByLogin($login)
    {
        $user = new User();
        $user->login = $login;
        return $user->findSaved();
    }


}