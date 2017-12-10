<?php

namespace GeoTool\Reader\Gpx;


use GeoTool\Entities\Point;
use Yaoi\String\Parser;

class Reader
{
    /** @var Parser */
    private $parser;

    /** @var Point */
    private $prevPoint;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return bool|Point
     */
    public function next()
    {
        do {
            $trk = $this->parser->inner('<trkpt', '</trkpt>');
            if ($trk->isEmpty()) {
                return false;
            }
            $point = new Point();
            $point->longitude = (float)$trk->inner('lon="', '"')->__toString();
            $point->latitude = (float)$trk->setOffset(0)->inner('lat="', '"')->__toString();
            $point->altitude = (float)$trk->setOffset(0)->inner('<ele>', '</ele>')->__toString(); //<ele>3617.73388671875</ele>
            $time = (string)$trk->setOffset(0)->inner('<time>', '</time>'); // <time>2017-11-09T01:00:32.830Z</time>
            $point->ut = strtotime($time);
            //echo $time, ' ', $point->ut, "\n";
            //$point->accuracy = (float)$trk->setOffset(0)->inner('<pdop>', '</pdop>'); // <pdop>7.3</pdop>

            if ($this->prevPoint && $this->prevPoint->ut > $point->ut) {
                echo "Skipping ", $trk, "\n";
                continue;
            } else {
                $this->prevPoint = $point;
                return $point;
            }
        } while (true);
    }

}