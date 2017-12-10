<?php

namespace Photo;


use GeoTool\Entities\Photo;
use GeoTool\Photo\Importer;

require_once __DIR__. '/../../env/conf.php';

class PhotoTest extends \PHPUnit_Framework_TestCase
{
    public function testExif()
    {
        $filePath = __DIR__ . '/IMG_001.jpg';

        $importer = new Importer();

        $photo = new Photo();
        $importer->readExif($filePath, $photo);

        $this->assertSame(1509811258, $photo->ut);
    }

}