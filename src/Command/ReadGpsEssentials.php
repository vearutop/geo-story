<?php

namespace GeoTool\Command;

use GeoTool\BatchSaver;
use GeoTool\Entities\Event;
use GeoTool\Entities\Point;
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
use GeoTool\Reader\Importer;
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
    /** @var Event */
    private $lastEvent;

    /** @var BatchSaver[] */
    private $batchSavers;

    public function performAction()
    {
        if (!file_exists($this->path)) {
            throw new \Exception(Expression::create('File ? not found', $this->path));
        }

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

        $importer = new Importer();

        while ($res = $pageQuery->query()->fetchAll()) {
            /** @var TrackElement $row */
            foreach ($res as $row) {
                $row->time /= 1000;

                $point = new Point();
                $point->ut = $row->time;
                $point->altitude = $row->altitude;
                $point->accuracy = $row->accuracy;
                $point->latitude = $row->latitude;
                $point->longitude = $row->longitude;
                $importer->addPoint($point);
            }
            $this->offset += $this->pageSize;
            $pageQuery->offset($this->offset);
        }

    }


}