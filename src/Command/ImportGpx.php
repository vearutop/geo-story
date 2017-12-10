<?php

namespace GeoTool\Command;


use GeoTool\Entities\User;
use GeoTool\Reader\Gpx\Reader;
use GeoTool\Reader\Importer;
use Phperf\Pipeline\Vector\DropAnomaly;
use Phperf\Pipeline\Vector\KalmanFilter;
use Phperf\Pipeline\Vector\MovingAverage;
use Phperf\Pipeline\Vector\Pipeline;
use Yaoi\Cli\Option;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\String\Parser;

class ImportGpx extends Command
{
    public $path;
    public $login;

    static function setUpDefinition(Definition $definition, $options)
    {
        $options->login = Option::create()->setIsUnnamed()->setIsRequired()->setDescription('Owner login');
        $options->path = Command\Option::create()->setIsUnnamed()->setIsRequired();
    }

    public function performAction()
    {

        $user = User::getByLogin($this->login);
        if (!$user) {
            $this->response->error("User not found: $this->login");
            return false;
        }

        ini_set('precision', 17);

        if (is_dir($this->path)) {
            if ($handle = opendir($this->path)) {


                while (false !== ($entry = readdir($handle))) {
                    if (strtolower(substr($entry, -4)) == '.gpx') {
                        //$latPipeline = new MovingAverage(2);
                        //$lonPipeline = new MovingAverage(2);
                        $latPipeline = (new Pipeline())
                            //->addProcessor(new KalmanFilter())
                        ;
                        $lonPipeline = (new Pipeline())
                            //->addProcessor(new KalmanFilter())
                        ;

                        echo "$entry\n";
                        $importer = new Importer($user->id);

                        $p = new Parser(file_get_contents($this->path . '/' . $entry));
                        $gpx = new Reader($p);

                        while ($point = $gpx->next()) {
                            $point->longitude = $lonPipeline->value($point->longitude);
                            $point->latitude = $latPipeline->value($point->latitude);
                            $importer->addPoint($point);
                        }
                        unset($importer);
                    }
                }

                closedir($handle);
            }
        } else {
            $importer = new Importer($user->id);
            $p = new Parser(file_get_contents($this->path));
            $gpx = new Reader($p);
            while ($point = $gpx->next()) {
                $importer->addPoint($point);
            }
            unset($importer);
        }
        $this->response->success('OK');

        return true;
    }


}