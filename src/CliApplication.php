<?php

namespace GeoTool;

use GeoTool\Command\AddStory;
use GeoTool\Command\AddUser;
use GeoTool\Command\Export;
use GeoTool\Command\ImportGpx;
use GeoTool\Command\ImportPhotos;
use GeoTool\Command\Migrate;
use GeoTool\Command\ReadGpsEssentials;
use Yaoi\Command\Application;
use Yaoi\Command\Definition;

class CliApplication extends Application
{
    public $migrate;
    public $addUser;
    public $addStory;
    public $importGpx;
    public $importPhotos;
    public $readGpsEssentials;
    public $export;
    public $merge;
    public $cut;

    static function setUpCommands(Definition $definition, $commandDefinitions)
    {
        $commandDefinitions->migrate = Migrate::definition();
        $commandDefinitions->addUser = AddUser::definition();
        $commandDefinitions->addStory = AddStory::definition();
        $commandDefinitions->importGpx = ImportGpx::definition();
        $commandDefinitions->importPhotos = ImportPhotos::definition();
        $commandDefinitions->readGpsEssentials = ReadGpsEssentials::definition();
        $commandDefinitions->export = Export::definition();
        $definition->name = 'gps-tool';
        $definition->description = 'GPS tracks analytics';
    }

}