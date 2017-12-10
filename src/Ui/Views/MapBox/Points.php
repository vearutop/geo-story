<?php

namespace GeoTool\Ui\Views\MapBox;

class Points extends Route
{
    public function render()
    {
        $coordinates = json_encode($this->coordinates);
        if (null === $this->id) {
            $this->id = 'points' . ++self::$seq;
        }
        echo <<<JSON
{
    "id": "{$this->id}",
    type: 'circle',
    source: loadPoints({$coordinates}),
    paint: {
        'circle-color': '#f23c57',
        'circle-radius': {
            'base': 2.0,
            'stops': [[12, 2], [22, 180]]
        }
    }
}
JSON;

/*
        echo <<<JSON
{
    "id": "points",
    "type": "symbol",
    "source": {
        "type": "geojson",
        "data": {
            "type": "FeatureCollection",
            "features": [{
                "type": "Feature",
                "geometry": {
                    "type": "Point",
                    "coordinates": [-77.03238901390978, 38.913188059745586]
                }
            }, {
                "type": "Feature",
                "geometry": {
                    "type": "Point",
                    "coordinates": [-122.414, 37.776]
                },
                "properties": {
                    "title": "Mapbox SF",
                    "icon": "harbor"
                }
            }]
        }
    },
    "layout": {
        "icon-image": "{icon}-15",
        "text-field": "{title}",
        "text-font": ["Open Sans Semibold", "Arial Unicode MS Bold"],
        "text-offset": [0, 0.6],
        "text-anchor": "top"
    }
}
JSON;
*/
    }


}