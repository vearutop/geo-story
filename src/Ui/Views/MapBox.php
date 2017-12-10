<?php

namespace GeoTool\Ui\Views;


use GeoTool\Ui\Views\MapBox\Images;
use GeoTool\Ui\Views\MapBox\Points;
use GeoTool\Ui\Views\MapBox\Route;
use Yaoi\Twbs\Layout;
use Yaoi\View\Hardcoded;

class MapBox extends Hardcoded
{
    /** @var Layout */
    private $layout;

    /** @var Route[] */
    private $routes = [];

    /** @var Points[] */
    private $points = [];

    /** @var Images */
    private $images;

    public function __construct(Layout $layout)
    {
        $this->layout = $layout;
    }

    public function addPoints(Points $points)
    {
        $this->points[] = $points;
        return $this;
    }

    public function addRoute(Route $route)
    {
        $this->routes[] = $route;
        return $this;
    }

    public function setImages(Images $images)
    {
        $this->images = $images;
        return $this;
    }

    public $minLon;
    public $maxLon;
    public $minLat;
    public $maxLat;

    public $centerLon;
    public $centerLat;

    public function render()
    {
        /** @var Route $route */
        foreach (array_merge($this->routes, $this->points) as $route) {
            $this->minLon = $this->minLon ? min($this->minLon, $route->minLon) : $route->minLon;
            $this->maxLon = $this->maxLon ? max($this->maxLon, $route->maxLon) : $route->maxLon;

            $this->minLat = $this->minLat ? min($this->minLat, $route->minLat) : $route->minLat;
            $this->maxLat = $this->maxLat ? max($this->maxLat, $route->maxLat) : $route->maxLat;
        }
        $this->centerLon = ($this->minLon + $this->maxLon) / 2;
        $this->centerLat = ($this->minLat + $this->maxLat) / 2;

        $this->layout->headScriptUrls[] = 'https://api.tiles.mapbox.com/mapbox-gl-js/v0.42.2/mapbox-gl.js';
        $this->layout->styleUrls[] = 'https://api.tiles.mapbox.com/mapbox-gl-js/v0.42.2/mapbox-gl.css';

        ?>
        <div id='map' style="width:100%;height:400px"></div>

        <script>
            mapboxgl.accessToken = 'pk.eyJ1IjoidmVhcnV0b3AiLCJhIjoiMjk5YWZiNDgzYzQyNWMxNzFhYzdlNzJmYmY5NjAwYzkifQ.MygXZ1-0lxkd2Zzpa1ypag';
            var map = new mapboxgl.Map({
                container: 'map',
                //style: 'mapbox://styles/mapbox/streets-v9',
                style: 'mapbox://styles/vearutop/cjabvzinw3quu2ro2zkuzg8u1',
                center: [<?=$this->centerLon?>, <?=$this->centerLat?>],
                zoom: 7
            });

            var positionMarker = {
                "type": "FeatureCollection",
                "features": [{
                    "type": "Feature",
                    "geometry": {
                        "type": "Point",
                        "coordinates": [0, 0]
                    }
                }]
            };

            map.on('load', function () {

                function loadPoints(coordinates) {
                    var src = {
                        type: 'geojson',
                        data: {
                            type: "FeatureCollection",
                            features: []
                        },
                        buffer: 0/*,
                        cluster: true,
                        clusterMaxZoom: 12,
                        clusterRadius: 20,
                        maxzoom: 12*/
                    };
                    for (var i = 0; i < coordinates.length; ++i) {
                        src.data.features[i] = {
                            type: "Feature",
                            geometry: {
                                type: "Point",
                                coordinates: coordinates[i]
                            }
                        }
                    }
                    return src;
                }

                // Add a single point to the map

                map.addSource('point', {
                    "type": "geojson",
                    "data": positionMarker
                });

                map.addLayer({
                    "id": "point",
                    "type": "circle",
                    "source": "point",
                    "paint": {
                        "circle-radius": 10,
                        "circle-color": "#3887be"
                    }
                });

                <?=$this->images?>

            <?php
                foreach ($this->routes as $route) {
                ?>
                map.addLayer(<?php $route->render(); ?>);

                <?php
                }

                foreach ($this->points as $points) {
                ?>
                map.addLayer(<?php $points->render(); ?>);

                <?php
                }
                ?>


                map.fitBounds([[
                    <?=$this->minLon?>,
                    <?=$this->minLat?>
                ], [
                    <?=$this->maxLon?>,
                    <?=$this->maxLat?>
                ]]);


                (function ($) {
                    $('.swipebox').swipebox();
                })(jQuery);
            });

        </script>

        <button type="button" class="btn btn-info" onclick="$('.marker').toggle();return false">Hide/show photos</button>
        <?php
    }
}