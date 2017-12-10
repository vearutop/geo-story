<?php

namespace GeoTool\Data;


use GeoTool\Entities\Photo;
use GeoTool\Entities\Segment10;
use GeoTool\Entities\Segment100;
use GeoTool\Entities\Segment5;
use GeoTool\Entities\Segment50;
use GeoTool\Entities\Segment500;
use GeoTool\Entities\Segment60s;
use GeoTool\Entities\Story;

class Queries
{
    /** @var Story */
    private $story;


    public $utTo;
    public $utFrom;
    private $userId;

    /**
     * Queries constructor.
     * @param Story $story
     */
    public function __construct(Story $story)
    {
        $this->story = $story;
        $this->utFrom = $story->utFrom;
        $this->utTo = $story->utTo;
        $this->userId = $story->userId;
    }

    /**
     * @return \Yaoi\Database\Query | Segment5[]
     */
    public function getSegments()
    {
        $cols = Segment10::columns();
        $res = Segment10::statement()
            ->where('? = ?', $cols->userId, $this->userId)
            ->order('? ASC', $cols->ut);
            //->where('? < 2.6', $cols->speed)
            //->where('? < 100', $cols->longitude)
        if ($this->utTo !== null) {
            $res->where('? <= ?', $cols->ut, $this->utTo);
        }
        if ($this->utFrom !== null) {
            $res->where('? >= ?', $cols->ut, $this->utFrom);
        }

        return $res->query();
    }

    /**
     * @return \Yaoi\Database\Query | Segment5[]
     */
    public function getPauses()
    {
        //return [];
        $cols = Segment100::columns();
        $res = Segment100::statement()
            ->where('? = ?', $cols->userId, $this->userId)
            ->order('? ASC', $cols->ut)
            //->limit(1)
            ->where('? > ?', $cols->time, 900);

        if ($this->utTo !== null) {
            $res->where('? <= ?', $cols->ut, $this->utTo);
        }
        if ($this->utFrom !== null) {
            $res->where('? >= ?', $cols->ut, $this->utFrom);
        }

        return $res->query();
    }


    /**
     * @return \Yaoi\Database\Query | Photo[]
     */
    public function getPhotos()
    {
        $cols = Photo::columns();
        $res = Photo::statement()
            ->select($cols->latitude, $cols->longitude, $cols->urlName)
            ->where('? = ?', $cols->storyId, $this->story->id)
            ->query();
        return $res;
    }


    /**
     * @return \Yaoi\Database\Query | Segment60s[]
     */
    public function getTimeMap()
    {
        $cols = Segment60s::columns();
        $res = Segment60s::statement()
            ->select($cols->ut, $cols->latitude, $cols->longitude)
            ->where('? = ?', $cols->userId, $this->userId)
            ->order('? ASC', $cols->ut);

        if ($this->utTo !== null) {
            $res->where('? <= ?', $cols->ut, $this->utTo);
        }
        if ($this->utFrom !== null) {
            $res->where('? >= ?', $cols->ut, $this->utFrom);
        }

        return $res->query();
    }

}