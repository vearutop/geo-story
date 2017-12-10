<?php

namespace GeoTool\Data;

class DickotomicIndex
{
    public $data;

    public function addPoint($x, $value)
    {
        $this->data[] = [$x, $value];
    }

    public function find($x)
    {
        $a = <<<JS
(function(){
    var low = 0;
    var high = points.length;
    var mid;
    var point;
    do {
        mid = Math.floor((low + high) / 2);
        point = points[mid];
        if (point[0] < dist) {
            low = mid
        } else {
            high = mid
        }
    } while (high - low > 1);

    return [point[1], point[2]];
})();
JS;
        $low = 0;
        $high = count($this->data);
        do {
            $mid = (int)(($low + $high) / 2);
            $point = $this->data[$mid];
            if ($point[0] < $x) {
                $low = $mid;
            } else {
                $high = $mid;
            }
        } while ($high - $low > 1);

        return $point[1];

    }

}