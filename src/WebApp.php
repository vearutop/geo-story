<?php

namespace GeoTool;


use GeoTool\Api\Api;
use GeoTool\Ui\Dashboard;
use Yaoi\Command;
use Yaoi\Twbs\Response;

class WebApp extends Command
{
    /**
     * @var Command
     */
    public $action;

    /**
     * @param Command\Definition $definition
     * @param \stdClass|static $options
     */
    static function setUpDefinition(Command\Definition $definition, $options)
    {
        $options->action = Command\Option::create()
            ->setDescription('Root action')
            ->setIsUnnamed()
            ->addToEnum(Dashboard::definition(), '')
            ->addToEnum(Dashboard::definition(), 'story')
            ->addToEnum(Api::definition())
        ;

        $definition->description = 'Geo Tool';
    }


    public function performAction()
    {
        $this->action->performAction();

        $response = $this->response;
        if ($response instanceof Response) {
            $layout = $response->getLayout();
            if ($layout !== null) {
                $layout->setTitle('GeoStory');
                $layout->headScriptUrls[] = '/js/leaflet-omnivore.min.js';
                $layout->headScriptUrls[] = '/js/mapbox.js';
                $layout->headScriptUrls[] = '/js/timemap.js';
                $layout->headScriptUrls[] = '/js/swipebox.js';

                $layout->styleUrls[] = '/css/mapbox.css';
                $layout->styleUrls[] = '/css/geostory.css';
                $layout->styleUrls[] = '/css/swipebox.css';
            }
        }
    }


}