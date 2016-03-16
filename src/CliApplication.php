<?php

namespace GeoTool;

use GeoTool\Command\Export;
use GeoTool\Command\Migrate;
use GeoTool\Command\ReadGpsEssentials;
use Yaoi\Command\Application;
use Yaoi\Command\Definition;

class CliApplication extends Application
{
    public $migrate;
    public $readGpsEssentials;
    public $export;

    static function setUpCommands(Definition $definition, $commandDefinitions)
    {
        $commandDefinitions->migrate = Migrate::definition();
        $commandDefinitions->readGpsEssentials = ReadGpsEssentials::definition();
        $commandDefinitions->export = Export::definition();
        $definition->name = 'gps-tool';
        $definition->description = 'GPS tracks analytics';
    }

}