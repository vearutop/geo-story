<?php

namespace GeoTool\Command;

use GeoTool\Data\Queries;
use GeoTool\Entities\Story;
use GeoTool\Entities\User;
use GeoTool\Photo\Importer;
use Yaoi\Command;
use Yaoi\Command\Definition;

class ImportPhotos extends Command
{
    public $user;
    public $story;
    public $path;

    static function setUpDefinition(Definition $definition, $options)
    {
        $options->user = Command\Option::create()->setIsUnnamed()->setIsRequired();
        $options->story = Command\Option::create()->setIsUnnamed()->setIsRequired();
        $options->path = Command\Option::create()->setIsUnnamed()->setIsRequired();
    }

    public function performAction()
    {

        if (!$handle = opendir($this->path)) {
            $this->response->error('Failed to open ' . $this->path);
        }

        $user = User::getByLogin($this->user);
        $story = Story::getByName($user->id, $this->story);

        $importer = new Importer($story);
        $importer->albumName = $this->user . '/' . $this->story;

        while (false !== ($entry = readdir($handle))) {
            if (strtolower(substr($entry, -4)) == '.jpg') {
                echo "$entry\n";

                $importer->addImageFile($this->path . '/' . $entry);
            }
        }

        unset($importer);
        closedir($handle);
        $this->response->success('OK');
    }
}