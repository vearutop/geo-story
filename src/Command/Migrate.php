<?php

namespace GeoTool\Command;

use GeoTool\Entities\Event;
use GeoTool\Entities\Photo;
use GeoTool\Entities\Point;
use GeoTool\Entities\Segment10;
use GeoTool\Entities\Segment100;
use GeoTool\Entities\Segment10k;
use GeoTool\Entities\Segment10s;
use GeoTool\Entities\Segment1k;
use GeoTool\Entities\Segment30;
use GeoTool\Entities\Segment5;
use GeoTool\Entities\Segment50;
use GeoTool\Entities\Segment500;
use GeoTool\Entities\Segment5k;
use GeoTool\Entities\Segment60s;
use GeoTool\Entities\Story;
use GeoTool\Entities\User;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\Database\Definition\Table;
use Yaoi\Log;

class Migrate extends Command
{
    public $wipe;
    public $dryRun;

    static function setUpDefinition(Definition $definition, $options)
    {
        $options->wipe = Command\Option::create()->setDescription('Recreate tables');
        $options->dryRun = Command\Option::create()->setDescription('Read-only mode');
        $definition->name = 'migrate';
        $definition->description = 'Actualize application data schema';
    }

    public function performAction()
    {
        /** @var Table[] $tables */
        $tables = array(
            Point::table(),
            Segment5::table(),
            Segment10::table(),
            Segment30::table(),
            Segment50::table(),
            Segment100::table(),
            Segment500::table(),
            Segment1k::table(),
            Segment5k::table(),
            Segment10k::table(),
            Segment10s::table(),
            Segment60s::table(),

            Event::table(),
            Photo::table(),
            Story::table(),
            User::table(),
        );

        $log = new Log('colored-stdout');
        if ($this->wipe) {
            foreach ($tables as $table) {
                $table->migration()->setLog($log)->setDryRun($this->dryRun)->rollback();
            }
        }
        foreach ($tables as $table) {
            $table->migration()->setLog($log)->setDryRun($this->dryRun)->apply();
        }
    }


}