<?php

namespace GeoTool\Command;

use GeoTool\Entities\Segment10;
use GeoTool\Entities\Segment100;
use GeoTool\Entities\Segment10k;
use GeoTool\Entities\Segment10s;
use GeoTool\Entities\Segment1k;
use GeoTool\Entities\Segment5;
use GeoTool\Entities\Segment500;
use GeoTool\Entities\Segment5k;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\Database\Definition\Table;

class Export extends Command
{
    public $from;
    public $to;
    public $out;
    /** @var Table */
    //public $segmentSize;

    static function setUpDefinition(Definition $definition, $options)
    {
        $options->from = Command\Option::create()->setType()->setIsRequired()
            ->setDescription('Time from');
        $options->to = Command\Option::create()->setType()->setIsRequired()
            ->setDescription('Time to');
        $options->out = Command\Option::create()->setType()->setIsRequired()
            ->setDescription('Output GPX filename');

        /*
        $options->segmentSize = Command\Option::create()->setIsRequired()
            ->addToEnum(Segment5::MIN_DISTANCE, Segment5::className())
            ->addToEnum(Segment10::MIN_DISTANCE, Segment10::className())
            ->addToEnum(Segment100::MIN_DISTANCE, Segment100::className())
            ->addToEnum(Segment500::MIN_DISTANCE, Segment500::className())
            ->addToEnum(Segment1k::MIN_DISTANCE, Segment1k::className())
            ->addToEnum(Segment5k::MIN_DISTANCE, Segment5k::className())
            ->addToEnum(Segment10k::MIN_DISTANCE, Segment10k::className())
            ;
        */
    }

    public function performAction()
    {
        $utFrom = strtotime($this->from);
        $utTo = strtotime($this->to);

        $cols = Segment10s::columns();

        $pageSize = 1000;
        $offset = 0;
        $statement = Segment10s::statement()
            ->where('? >= ? AND ? <= ?', $cols->ut, $utFrom, $cols->ut, $utTo)
            ->order('? DESC', $cols->ut)
            ->limit($pageSize);

        $head = <<<'GPX'
<?xml version='1.0' encoding='UTF-8' standalone='yes' ?><gpx version="1.1" creator="GPS Essentials - http://www.gpsessentials.com" xmlns="http://www.topografix.com/GPX/1/1"><trk><name>Track-150901-201618 Hcm</name><desc></desc><number>12</number><trkseg>
GPX;
        $tail = <<<'GPX'
</trkseg></trk></gpx>
GPX;

        $out = fopen($this->out, 'w');
        fwrite($out, $head);

        $hours = array();
        $lastHour = '';

        date_default_timezone_set('UTC');
        do {
            $res = $statement->query()->fetchAll();
            echo $statement, PHP_EOL;
            echo count($res), PHP_EOL;

            $items = '';
            /** @var Segment100 $segment */
            foreach ($res as $segment) {
                $dateTime = date('c', $segment->ut);
                $dateTime = substr($dateTime, 0, 19) . 'Z';

                $hour = substr($dateTime, 0, 13);
                if ($hour !== $lastHour) {
                    $hours [$segment->ut]= array(
                        'hour' => $dateTime,
                        'lat' => $segment->latitude,
                        'lon' => $segment->longitude,
                        'speed' => $segment->speed
                    );
                    $lastHour = $hour;
                }

                $item = <<<GPX
<trkpt lat="$segment->latitude" lon="$segment->longitude"><ele>$segment->elevation</ele><speed>$segment->speed</speed><time>$dateTime</time></trkpt>
GPX;

                fwrite($out, $item);
            }


            $offset += $pageSize;
            $statement->offset($offset);
        }
        while ($res);


        fwrite($out, $tail);
        date_default_timezone_set('NZ');
        foreach ($hours as $ut => &$data) {
            $data['local'] = date('c', $ut);
        }
        file_put_contents($this->out . '.json', json_encode($hours));
    }


}