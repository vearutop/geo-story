<?php

namespace GeoTool\Ui\Views\GeoChart;

use Phperf\HighCharts\HighCharts;
use Yaoi\Io\Content\Rows;
use Yaoi\Io\Content\Text;
use Yaoi\Twbs\Response;
use Yaoi\Twbs\Views\TabbedBlocks;
use Yaoi\View\Hardcoded;
use Yaoi\View\Renderer;
use Yaoi\View\Stack;

class GeoChart extends Hardcoded
{
    /** @var Renderer */
    public $map;

    /** @var HighCharts[] */
    public $chartsByDate = [];

    /** @var HighCharts[] */
    public $chartsByDist = [];

    /** @var HighCharts[] */
    public $chartsByTime = [];

    /** @var string[] */
    public $totals = [];

    /** @var Response */
    public $response;


    /*
     * --------------------------------------------------------------------
     * |  fixed, w30%, h100% |                charts                      |
     * |         map         |                                            |
     *
     */


    public function render()
    {
        $tb = new TabbedBlocks();
        //$tb->addBlock('Photo', '<div id="photo-holder"></div>');
        $tb->addBlock('Stats', $this->response->renderMessage(new Rows(new \ArrayIterator($this->totals))));
        $tb->addBlock('By Distance', implode('', $this->chartsByDist));
        $tb->addBlock('By Date', implode('', $this->chartsByDate));
        $tb->addBlock('By Moving Time', implode('', $this->chartsByTime));


        ?>
        <style>
            .highcharts {
                height: 250px;
            }

            #geo-chart {
                position: relative;
            }

            .map {
                position: relative;
                /*width: 50%;*/
                float: left;
            }

            .charts {
                /*margin-left: 50%;
                padding-left: 10px;*/
                position: relative;
            }
        </style>
        <div id="geo-chart">
            <div class="map col-md-6">
                <div id="map-holder">
                    <?= $this->map ?>
                </div>
            </div>


            <div class="charts col-md-6">
                <?= $tb ?>
            </div>

        </div>
        <script src="/js/jquery.fixonscroll.js"></script>
        <script>
            $(function (){
                //var c = $('#geo-chart');
                $('#map-holder').fixOnScroll({
                    boxTop: 50,
                    fixedTop: 50
                });
            });
        </script>
        <?php
    }
}