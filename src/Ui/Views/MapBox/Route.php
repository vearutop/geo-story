<?php

namespace GeoTool\Ui\Views\MapBox;


use Phperf\HighCharts\Colors;
use Yaoi\View\Hardcoded;

ini_set('precision', 14);

class Route extends Hardcoded
{

    protected $coordinates = [];
    protected $id;
    protected static $seq = 0;
    protected $color;

    public $minLon;
    public $maxLon;
    public $minLat;
    public $maxLat;



    public function addPoint($lon, $lat)
    {
        $lon = (float)$lon;
        $lat = (float)$lat;

        $this->minLon = $this->minLon ? min($this->minLon, $lon) : $lon;
        $this->maxLon = $this->maxLon ? max($this->maxLon, $lon) : $lon;

        $this->minLat = $this->minLat ? min($this->minLat, $lat) : $lat;
        $this->maxLat = $this->maxLat ? max($this->maxLat, $lat) : $lat;

        $this->coordinates[] = [$lon, $lat];
    }

    /**
     * @param mixed $id
     * @return Route
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param mixed $color
     * @return Route
     */
    public function setColor($color)
    {
        $this->color = $color;
        return $this;
    }



    public function render()
    {
        $coordinates = json_encode($this->coordinates);
        if (null === $this->id) {
            $this->id = 'route' . ++self::$seq;
        }
        if (null === $this->color) {
            $this->color = Colors::next();
        }

        echo <<<JSON
{
    "id": "{$this->id}",
    "type": "line",
    "source": {
        "type": "geojson",
        "data": {
            "type": "Feature",
            "properties": {
            },
            "geometry": {
                "type": "LineString",
                "coordinates": {$coordinates}
            }
        }
    },
    "layout": {
        "line-join": "round",
        "line-cap": "round"
    },
    "paint": {
        "line-color": "{$this->color}",
        "line-width": 3
    }
}
JSON;

    }


}