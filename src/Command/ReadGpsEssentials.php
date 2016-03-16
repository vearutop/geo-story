<?php

namespace GeoTool\Command;

use GeoTool\BatchSaver;
use GeoTool\Entities\Segment10;
use GeoTool\Entities\Segment100;
use GeoTool\Entities\Segment10s;
use GeoTool\Entities\Segment1k;
use GeoTool\Entities\Segment5;
use GeoTool\Entities\Segment500;
use GeoTool\Entities\Segment5k;
use GeoTool\Entities\Segment10k;
use GeoTool\Entities\Segment60s;
use GeoTool\Reader\GPSEssentials\GPSEssentials;
use GeoTool\Reader\GPSEssentials\TrackElement;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\Database;
use Yaoi\Log;
use Yaoi\String\Expression;

class ReadGpsEssentials extends Command
{
    public $path;
    public $from;
    public $to;

    static function setUpDefinition(Definition $definition, $options)
    {
        $options->path = Command\Option::create()
            ->setDescription('Path to "Waypoints" file')
            ->setIsUnnamed()
            ->setIsRequired();
        $options->from = Command\Option::create()->setType()
            ->setDescription('Time from');

        $options->to = Command\Option::create()->setType()
            ->setDescription('Time to');

        $definition->description = 'Import GPS Essentials database (sqlite)';
        $definition->name = 'read-gps-essentials';

    }

    private $count;
    private $offset = 0;
    private $pageSize = 1000;
    private $segments;
    private $lastPoints = array();

    public function performAction()
    {
        if (!file_exists($this->path)) {
            throw new \Exception(Expression::create('File ? not found', $this->path));
        }

        $GPSEssentials = new GPSEssentials($this->path);
        $database = new Database('sqlite:///' . realpath($this->path));
        $database->log(new Log('colored-stdout'));
        TrackElement::bindDatabase($database);
        //$this->count = $database->query("SELECT COUNT(1) AS c FROM ?", TrackElement::table())->fetchRow('c');


        $pageQuery = TrackElement::statement()
            ->order("? ASC", TrackElement::columns()->id)
            ->limit($this->pageSize);

        if ($this->from) {
            $pageQuery->where('? >= 1000 * ?', TrackElement::columns()->time, strtotime($this->from));
        }

        if ($this->to) {
            $pageQuery->where('? <= 1000 * ?', TrackElement::columns()->time, strtotime($this->to));
        }

        $this->segments = array(
            Segment5::className(),
            Segment10::className(),
            Segment100::className(),
            Segment500::className(),
            Segment1k::className(),
            Segment5k::className(),
            Segment10k::className(),
        );

        $timeSegments = array(
            Segment10s::className(),
            Segment60s::className()
        );

        

        /** @var BatchSaver[] $batchSavers */
        $batchSavers = array();
        foreach ($this->segments as $segment) {
            $batchSavers[$segment] = new BatchSaver();
        }
        foreach ($timeSegments as $segment) {
            $batchSavers[$segment] = new BatchSaver();
        }

        while ($res = $pageQuery->query()->fetchAll()) {
            /** @var TrackElement $row */
            foreach ($res as $row) {
                $row->time /= 1000;

                /** @var Segment5|string $segment */
                foreach ($this->segments as $segment) {
                    if (!isset($this->lastPoints[$segment])) {
                        $this->lastPoints[$segment] = $row;
                    }
                    /** @var TrackElement $lastPoint */
                    $lastPoint = $this->lastPoints[$segment];
                    if (($distance = GPSEssentials::distance($lastPoint, $row)) > $segment::MIN_DISTANCE) {
                        /** @var Segment5 $segmentItem */
                        $segmentItem = new $segment;
                        $segmentItem->distance = $distance;
                        $segmentItem->latitude = $row->latitude;
                        $segmentItem->longitude = $row->longitude;
                        $segmentItem->ut = $row->time;
                        $segmentItem->altitude = $row->altitude;
                        $segmentItem->time = $row->time - $lastPoint->time;
                        if ($segmentItem->time) {
                            $segmentItem->speed = $segmentItem->distance / $segmentItem->time;
                        }

                        $segmentItem->elevation = $row->altitude - $lastPoint->altitude;
                        $batchSavers[$segment]->add($segmentItem);
                        //$segmentItem->save();
                        $this->lastPoints[$segment] = $row;
                    }
                }

                /** @var Segment10s|string $segment */
                foreach ($timeSegments as $segment) {
                    if (!isset($this->lastPoints[$segment])) {
                        $this->lastPoints[$segment] = $row;
                    }
                    /** @var TrackElement $lastPoint */
                    $lastPoint = $this->lastPoints[$segment];

                    if ($row->time - $lastPoint->time > $segment::MIN_TIME) {
                        /** @var Segment5 $segmentItem */
                        $segmentItem = new $segment;
                        $segmentItem->distance = GPSEssentials::distance($lastPoint, $row);
                        $segmentItem->latitude = $row->latitude;
                        $segmentItem->longitude = $row->longitude;
                        $segmentItem->ut = $row->time;
                        $segmentItem->altitude = $row->altitude;
                        $segmentItem->time = $row->time - $lastPoint->time;
                        if ($segmentItem->time) {
                            $segmentItem->speed = $segmentItem->distance / $segmentItem->time;
                        }

                        $segmentItem->elevation = $row->altitude - $lastPoint->altitude;
                        $batchSavers[$segment]->add($segmentItem);
                        //$segmentItem->save();
                        $this->lastPoints[$segment] = $row;
                    }
                }

            }
            $this->offset += $this->pageSize;
            $pageQuery->offset($this->offset);
        }

        foreach ($batchSavers as $batchSaver) {
            $batchSaver->flush();
        }
    }


}