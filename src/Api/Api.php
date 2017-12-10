<?php

namespace GeoTool\Api;

use Swaggest\Json\Json;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\Io\Response\ArrayResponse;
use Yaoi\Twbs\Response;

class Api extends Command
{
    /** @var Command */
    public $action;

    static function setUpDefinition(Definition $definition, $options)
    {
        $options->action = Command\Option::create()
            ->setDescription('Root action')
            ->setIsUnnamed()
            ->addToEnum(Crop::definition());

        $definition->description = 'Geo Tool';

    }

    public function performAction()
    {
        $parentResponse = $this->response;
        if ($parentResponse instanceof Response) {
            $parentResponse->setLayout(null);
        }

        $response = new ArrayResponse();
        $this->action->response = $response;
        $this->action->performAction();
        header('Content-Type: application/json; charset=utf8');

        $result = $response->result;
        if (isset($response->result['content'])) {
            if (count($response->result['content']) === 1) {
                $result = $response->result['content'][0];
            }
        }
        echo new Json($result);
    }


}