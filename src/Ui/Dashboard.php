<?php

namespace GeoTool\Ui;


use GeoTool\Data\Queries;
use GeoTool\Entities\Segment10k;
use GeoTool\Entities\Story;
use GeoTool\Entities\User;
use GeoTool\Ui\Views\GeoChart\GeoChart;
use GeoTool\Ui\Views\MapBox;
use Phperf\HighCharts\Colors;
use Phperf\HighCharts\HighCharts;
use Phperf\HighCharts\Series;
use Phperf\HighCharts\SplitSeries;
use Phperf\Pipeline\Vector\Custom;
use Phperf\Pipeline\Vector\DropAnomaly;
use Phperf\Pipeline\Vector\MovingAverage;
use Phperf\Pipeline\Vector\Pipeline;
use Swaggest\Json\RawJson;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\Twbs\Response;

class Dashboard extends Command
{
    public $login;
    public $storyName;

    static function setUpDefinition(Definition $definition, $options)
    {
        $options->login = Command\Option::create()->setIsUnnamed()->setIsRequired();
        $options->storyName = Command\Option::create()->setIsUnnamed()->setIsRequired();
    }

    /** @var HighCharts[] */
    private $chartsByDate = [];
    /** @var HighCharts[] */
    private $chartsByDist = [];
    /** @var HighCharts[] */
    private $chartsByMovingTime = [];

    private $prevUt;
    private $prevDist;
    private $prevMovingTime;

    /** @var User */
    private $user;
    /** @var Story */
    private $story;

    public function performAction()
    {
        if (empty($this->storyName) || empty($this->login)) {
            $this->response->status = Response::STATUS_BAD_REQUEST;
            $this->response->error("Bad url");
            return false;
        }

        if (!$this->user = User::getByLogin($this->login)) {
            $this->response->status = Response::STATUS_NOT_FOUND;
            $this->response->error("User not found: " . $this->login);
            return false;
        }

        if (!$this->story = Story::getByName($this->user->id, $this->storyName)) {
            $this->response->status = Response::STATUS_NOT_FOUND;
            $this->response->error("Story not found: " . $this->storyName);
            return false;
        }


        Colors::next();
        Colors::next();

        if ($this->story->timezone) {
            date_default_timezone_set($this->story->timezone);
        }

        $response = $this->response;
        if ($response instanceof Response) {
            $layout = $response->getLayout();
        } else {
            throw new \Exception("Unknown layout");
        }

        ini_set('precision', 17);
        $q = new Queries($this->story);


        $pausePoints = new MapBox\Points();
        $pauses = $q->getPauses();
        $pauseIntervals = [];
        foreach ($pauses as $pause) {
            $pauseIntervals[] = [$pause->ut - $pause->time, $pause->ut];
            $pausePoints->addPoint($pause->longitude, $pause->latitude);
        }

        $res = $q->getSegments();

        $gc = new GeoChart();


        $altPipeline = (new Pipeline())
            ->addProcessor(new DropAnomaly(0.1))
            ->addProcessor(new MovingAverage(7))
            ->addProcessor(new Custom(function ($v) {
                return round($v);
            }));
        $altByDate = $this->makeHcByDate('Altitude (m)')->setPipeline($altPipeline);


        $speedPipeline = (new Pipeline())
            ->addProcessor(new DropAnomaly(0.7))
            ->addProcessor(new MovingAverage(10))
            ->addProcessor(new Custom(function ($v) {
                    return round(3.6 * $v, 2);
                })
            );

        $speedByDate = $this->makeHcByDate('Speed (km/h)')->setPipeline($speedPipeline);
        $distanceByDate = $this->makeHcByDate('Distance (m)');
        $ascByDate = $this->makeHcByDate('Ascending (m)');
        $descByDate = $this->makeHcByDate('Descending (m)');


        $altByDist = $this->makeHcByDist('Altitude (m)');
        $speedByDist = $this->makeHcByDist('Speed (km/h)');
        $ascByDist = $this->makeHcByDist('Ascending (m)');
        $descByDist = $this->makeHcByDist('Descending (m)');

        $altByTime = $this->makeHcByMovingTime('Altitude (m)');
        $speedByTime = $this->makeHcByMovingTime('Speed (km/h)');
        $ascByTime = $this->makeHcByMovingTime('Ascending (m)');
        $descByTime = $this->makeHcByMovingTime('Descending (m)');

        $mapBox = new MapBox($layout);

        $images = new MapBox\Images();
        foreach ($q->getPhotos() as $photo) {
            $images->addPhoto($photo);
        }
        $mapBox->setImages($images);


        $route = new MapBox\Route();

        $prevRouteTs = null;
        $prevTs = null;
        $dist = 0;
        $movingTime = 0;
        $pauseTime = 0;

        $totalAsc = 0;
        $totalDesc = 0;


        $timemapData = [];
        $distmapData = [];
        $movingTimeData = [];

        $prevAlt = null;


        $pause = reset($pauseIntervals);

        /** @var Segment10k $item */
        foreach ($res as $item) {
            $ts = $item->ut;

            while ($pause !== false && $ts > $pause[1]) {
                foreach ($this->chartsByDist as $chart) {
                    $chart->addOption('xAxis', 'plotLines', null, [
                        'color' => '#eee',
                        'width' => 1,
                        'value' => $dist,
                    ]);
                }
                $pause = next($pauseIntervals);
            }

            $inPause = false;

            if ($pause !== false && $ts >= $pause[0] && $ts <= $pause[1]) {
                $pauseTime += $item->time;
                $inPause = true;
            } else {
                $movingTime += $item->time;
                $dist += $item->distance;
            }



            $uts = 1000 * $ts;
            if ($prevRouteTs === null) {
                $prevRouteTs = $ts;
            }

            if ($ts - $prevRouteTs > 3600) {
                //if ($ts - $prevTs < 30) {
                $route->addPoint($item->longitude, $item->latitude);
                //}
                $mapBox->addRoute($route);
                $route = new MapBox\Route();
            }

            $lastAlt = $altPipeline->getLastValue();
            if ($lastAlt != 0) {
                if ($prevAlt === null) {
                    $prevAlt = $lastAlt;
                } else {
                    $delta = $lastAlt - $prevAlt;
                    $prevAlt = $lastAlt;

                    if ($delta > 0) {
                        $totalAsc += $delta;
                    } else {
                        $totalDesc += $delta;
                    }
                }
            }

            //$speed = round($item->speed * 3.6, 2);

            $route->addPoint($item->longitude, $item->latitude);

            if (!$this->prevUt || $ts - $this->prevUt > 60) {
                $timemapData[] = [$uts, $item->longitude, $item->latitude];
                $distanceByDate->addRow($uts, round($dist));
                $ascByDate->addRow($uts, $totalAsc);
                $descByDate->addRow($uts, $totalDesc);
                $altByDate->addRow($uts, $item->altitude);
                $speedByDate->addRow($uts, $item->speed);
            }

            if (!$this->prevDist || $dist - $this->prevDist > 50) {
                $d = round($dist);
                $distmapData[] = [$d, $item->longitude, $item->latitude];
                $speedByDist->addRow($d, $speedPipeline->getLastValue());
                $altByDist->addRow($d, $lastAlt);
                $ascByDist->addRow($d, $totalAsc);
                $descByDist->addRow($d, $totalDesc);
            }

            if (!$this->prevMovingTime || $movingTime - $this->prevMovingTime > 60) {
                $mt = round($movingTime / 3600, 2);
                $movingTimeData[] = [$mt, $item->longitude, $item->latitude];
                $speedByTime->addRow($mt, $speedPipeline->getLastValue());
                $altByTime->addRow($mt, $lastAlt);
                $ascByTime->addRow($mt, $totalAsc);
                $descByTime->addRow($mt, $totalDesc);
                $this->prevMovingTime = $movingTime;
            }

            $prevTs = $ts;
            $prevRouteTs = $ts;
        }

        $mapBox->addRoute($route);
        $mapBox->addPoints($pausePoints);

        $gc->chartsByDate = $this->chartsByDate;
        $gc->chartsByDist = $this->chartsByDist;
        $gc->chartsByTime = $this->chartsByMovingTime;


        $gc->map = $mapBox;

        $gc->response = $this->response;
        $total = [
            'Distance' => round($dist/1000, 2) . 'km',
            'Total Ascending' => $totalAsc . 'm',
            'Total Descending' => $totalDesc . 'm',
            'Pause Time' => round($pauseTime / 3600, 2) . 'h',
            'Moving Time' => round($movingTime / 3600, 2) . 'h'
        ];

        foreach ($total as $k => $v) {
            $gc->totals[] = ['Stat' => $k, 'Value' => $v];
        }

        //$response->addContent($mapBox);

        $response->addContent('<script>timeMap.set(' . json_encode($timemapData) . ');</script>');
        $response->addContent('<script>distanceMap.set(' . json_encode($distmapData) . ');</script>');
        $response->addContent('<script>movingTimeMap.set(' . json_encode($movingTimeData) . ');</script>');

        foreach ($this->chartsByDate as $name => $chart) {
            //$gc->chartsByDate .= $chart;
            //$this->response->addContent($chart->__toString());
        }
        $response->addContent($gc);


        return true;
        //$this->response->addContent(new Rows($res));
    }


    private function makeHcByDist($name)
    {
        $hc = new HighCharts();
        $hc->addOption('legend', 'enabled', false);
        //$hcDist->setType(HighCharts\Series::TYPE_COLUMN);
        $hc->setYTitle($name);
        $hc->addOption('plotOptions', 'series', 'point', 'events', 'mouseOver', new RawJson(<<<JS
distanceMap.highChartsHover
JS
        ));
        $this->chartsByDist[$name] = $hc;

        return new SplitSeries($hc, 1000);

    }


    private function makeHcByMovingTime($name)
    {
        $hc = new HighCharts();
        $hc->addOption('legend', 'enabled', false);
        //$hcDist->setType(HighCharts\Series::TYPE_COLUMN);
        $hc->setYTitle($name);
        $hc->addOption('plotOptions', 'series', 'point', 'events', 'mouseOver', new RawJson(<<<JS
movingTimeMap.highChartsHover
JS
        ));
        $this->chartsByMovingTime[$name] = $hc;

        return new SplitSeries($hc, 1000);

    }


    private function makeHcByDate($name)
    {
        $hc = new HighCharts();
        $hc->addOption('legend', 'enabled', false);
        $hc->withDateAxis();
        //$hcDist->setType(HighCharts\Series::TYPE_COLUMN);
        //$hc->setTitle($name);
        $hc->setYTitle($name);
        $series = new Series();
        $series->setName('default');
        $series->setId('default');
        $hc->addSeries($series);
        $hc->addOption('plotOptions', 'series', 'point', 'events', 'mouseOver', new RawJson(<<<JS
timeMap.highChartsHover
JS
        ));

        $this->chartsByDate[$name] = $hc;

        return new SplitSeries($hc, 3600 * 1000);
    }
}