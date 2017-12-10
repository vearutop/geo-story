<?php

namespace GeoTool\Api;

use GeoTool\Entities\Event;
use Yaoi\Command;
use Yaoi\Command\Definition;

class Crop extends Command
{
    static function setUpDefinition(Definition $definition, $options)
    {
    }

    public function performAction()
    {
        $result = new Event();
        $result->altitudeStart = 1234;
        $this->response->addContent($result);
    }
}