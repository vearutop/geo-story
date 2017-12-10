<?php

namespace GeoTool\Reader;

use GeoTool\BatchSaver;
use GeoTool\Calc;
use GeoTool\Entities\Event;
use GeoTool\Entities\Point;
use GeoTool\Entities\Segment10;
use GeoTool\Entities\Segment100;
use GeoTool\Entities\Segment10s;
use GeoTool\Entities\Segment1k;
use GeoTool\Entities\Segment30;
use GeoTool\Entities\Segment5;
use GeoTool\Entities\Segment50;
use GeoTool\Entities\Segment500;
use GeoTool\Entities\Segment5k;
use GeoTool\Entities\Segment10k;
use GeoTool\Entities\Segment60s;

class Importer
{
    /** @var array */
    private $segments;
    /** @var array */
    private $timeSegments;

    /** @var BatchSaver[] */
    private $batchSavers;

    private $eventSegment;

    /** @var Point[] */
    private $lastPoints = array();
    /** @var Event */
    private $lastEvent;

    private $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;

        $this->segments = array(
            Segment5::className(),
            Segment10::className(),
            Segment30::className(),
            Segment50::className(),
            Segment100::className(),
            Segment500::className(),
            Segment1k::className(),
            Segment5k::className(),
            Segment10k::className(),
        );

        $this->timeSegments = array(
            Segment10s::className(),
            Segment60s::className()
        );

        $this->batchSavers = array();
        foreach ($this->segments as $segment) {
            $b = new BatchSaver();
            //$b->pageSize = 3;
            $this->batchSavers[$segment] = $b;
        }
        foreach ($this->timeSegments as $segment) {
            $b = new BatchSaver();
            //$b->pageSize = 3;
            $this->batchSavers[$segment] = $b;
        }

        $this->batchSavers[Point::className()] = new BatchSaver();

        $this->eventSegment = Segment60s::className();

    }

    public function addPoint(Point $point) {
        $this->batchSavers[Point::className()]->add($point);

        //echo $point->ut, "\n";
        /** @var Segment5|string $segment */
        foreach ($this->segments as $segment) {
            if (!isset($this->lastPoints[$segment])) {
                $this->lastPoints[$segment] = $point;
            }
            /** @var Point $lastPoint */
            $lastPoint = $this->lastPoints[$segment];
            if (($distance = Calc::distance($lastPoint, $point)) > $segment::MIN_DISTANCE) {
                /** @var Segment5 $segmentItem */
                $segmentItem = new $segment;
                $segmentItem->userId = $this->userId;
                $segmentItem->distance = $distance;
                $segmentItem->latitude = 0.5 * ($point->latitude + $lastPoint->latitude);
                $segmentItem->longitude = 0.5 * ($point->longitude + $lastPoint->longitude);
                $segmentItem->ut = $point->ut;
                $segmentItem->altitude = 0.5 * ($point->altitude + $lastPoint->altitude);
                $segmentItem->time = $point->ut - $lastPoint->ut;
                if ($segmentItem->time) {
                    $segmentItem->speed = $segmentItem->distance / $segmentItem->time;
                }

                $segmentItem->elevation = $point->altitude - $lastPoint->altitude;
                $this->batchSavers[$segment]->add($segmentItem);
                $this->lastPoints[$segment] = $point;
            }
        }

        /** @var Segment10s|string $segment */
        foreach ($this->timeSegments as $segment) {
            if (!isset($this->lastPoints[$segment])) {
                $this->lastPoints[$segment] = $point;
            }
            /** @var Point $lastPoint */
            $lastPoint = $this->lastPoints[$segment];

            if ($point->ut - $lastPoint->ut > $segment::MIN_TIME) {
                /** @var Segment10s $segmentItem */
                $segmentItem = new $segment;
                $segmentItem->userId = $this->userId;
                $segmentItem->distance = Calc::distance($lastPoint, $point);
                $segmentItem->latitude = $point->latitude;
                $segmentItem->longitude = $point->longitude;
                $segmentItem->ut = $point->ut;
                $segmentItem->altitude = $point->altitude;
                $segmentItem->time = $point->ut - $lastPoint->ut;
                if ($segmentItem->time) {
                    $segmentItem->speed = $segmentItem->distance / $segmentItem->time;
                }

                $segmentItem->elevation = $point->altitude - $lastPoint->altitude;
                $this->batchSavers[$segment]->add($segmentItem);
                //$segmentItem->save();
                $this->lastPoints[$segment] = $point;

                // check events
                if ($segment === $this->eventSegment) {
                    $this->updateEvent($segmentItem);
                }

            }
        }
    }

    private function updateEvent(Segment5 $row)
    {
        if (null === $this->lastEvent) {
            $this->lastEvent = $event = new Event();

            $event->utStart = $row->ut;
            $event->longitudeStart = $row->longitude;
            $event->latitudeStart = $row->latitude;

            $event->altitudeStart = $row->altitude;

            $event->minSpeed = $event->maxSpeed = $event->avgSpeed = $event->avgMovingSpeed = $row->speed;

            $event->ascending = 0;
            $event->descending = 0;

            $event->stopTime = 0;
            $event->stops = 0;
        }

        $event = $this->lastEvent;

        if ($row->altitude > 0) {
            $event->ascending += $row->altitude;
        } else {
            $event->descending += -$row->altitude;
        }
        $event->maxSpeed = max($event->maxSpeed, $row->speed);
        $event->minSpeed = min($event->minSpeed, $row->speed);


        if ($event->avgSpeed >= 2 * $row->speed || $event->avgSpeed <= 0.5 * $row->speed) {
            $event->latitudeEnd = $row->latitude;
            $event->longitudeEnd = $row->longitude;
            $event->distance = Calc::rawDistance($event->latitudeStart, $event->longitudeStart,
                $event->latitudeEnd, $event->longitudeEnd);
            $event->utEnd = $row->ut;
            $event->totalTime = $event->utEnd - $event->utStart;
            if (!$event->totalTime) {
                return;
            }
            $event->avgSpeed = $event->distance / $event->totalTime;

            $event->altitudeEnd = $row->altitude;
            $event->save();

            $this->lastEvent = null;
            //print_r($event);
            //die();

        }
    }

    public function __destruct()
    {
        foreach ($this->batchSavers as $batchSaver) {
            $batchSaver->flush();
        }
    }


}