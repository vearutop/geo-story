#!/usr/bin/env bash

test ! -e ./vendor/phperf/highcharts/.git && rm -rf ./vendor/phperf/highcharts && git clone git@github.com:phperf/highcharts.git -b master ./vendor/phperf/highcharts
test ! -e ./vendor/php-yaoi/php-yaoi/.git && rm -rf ./vendor/php-yaoi/php-yaoi && git clone git@github.com:php-yaoi/php-yaoi.git -b master ./vendor/php-yaoi/php-yaoi
test ! -e ./vendor/php-yaoi/twbs/.git && rm -rf ./vendor/php-yaoi/twbs && git clone git@github.com:php-yaoi/twbs.git -b master ./vendor/php-yaoi/twbs
test ! -e ./vendor/phperf/pipeline/.git && rm -rf ./vendor/phperf/pipeline && git clone git@github.com:phperf/pipeline.git -b master ./vendor/phperf/pipeline
