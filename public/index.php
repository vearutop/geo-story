<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../env/conf.php';

set_error_handler(
    function ($severity, $message, $file, $line) {
        throw new \ErrorException($message, $severity, $severity, $file, $line);
    }
);


if (isset($_COOKIE['tz'])) {
    date_default_timezone_set($_COOKIE['tz']);
}
\Yaoi\Twbs\Runner::create()->run(\GeoTool\WebApp::definition());
