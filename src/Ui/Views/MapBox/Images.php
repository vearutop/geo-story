<?php

namespace GeoTool\Ui\Views\MapBox;

use GeoTool\Entities\Photo;
use Yaoi\View\Hardcoded;

class Images extends Hardcoded
{
    private $photos = [];

    public function addPhoto(Photo $photo)
    {
        $this->photos[] = [$photo->longitude, $photo->latitude, $photo->urlName];
        return $this;
    }


    public function render()
    {
        $photosJson = json_encode($this->photos);
        echo <<<JS
{$photosJson}.
forEach(function (marker) {
    // create a DOM element for the marker
    //var el = document.createElement('img');
    //el.className = 'marker';
    //el.src = marker[2] + '.th.jpg';

// <a href="big/image.jpg" class="swipebox" title="My Caption">
//<img src="small/image.jpg" alt="image">
//</a>

    var el = $('<a href="' + marker[2] + '.jpg" class="swipebox"><img src="' + marker[2] + '.th.jpg" class="marker" /></a>')

/*
    $(el).click(function () {
        window.alert(marker[2] + '.jpg');
    })
    */

/*
    $(el).hover(function(){
        $('#photo-holder').html($('<img src="'+ marker[2] + '.sm.jpg" />').click(
            function(){
                alert(1)
            }
        ));

    })
*/
    // add marker to map
    new mapboxgl.Marker(el[0])
        .setLngLat([marker[0], marker[1]])
        .addTo(map);
});

JS;

    }

}