<?php

namespace Reader;

use GeoTool\Reader\GPSEssentials\GPSEssentials;
use GeoTool\Reader\GPSEssentials\TrackElement;
use Yaoi\Database;
use Yaoi\Test\PHPUnit\TestCase;

class GPXTest extends TestCase
{
    public function testDistance() {
        $from = new TrackElement();
        $from->latitude = 104.08221435546875;
        $from->longitude = 22.36478042602539;


        $to = new TrackElement();
        $to->latitude = 104.95915222167969;
        $to->longitude = 21.539703369140625;

        var_dump(GPSEssentials::distance($from, $to));
    }
}


