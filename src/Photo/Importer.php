<?php

namespace GeoTool\Photo;


use Eventviva\ImageResize;
use GeoTool\Data\DickotomicIndex;
use GeoTool\Data\Queries;
use GeoTool\Entities\Photo;
use GeoTool\Entities\Story;

class Importer
{
    /** @var Story */
    private $story;



    public $albumName;
    public $utMap = [];

    /** @var DickotomicIndex */
    private $index;

    /**
     * Importer constructor.
     * @param Story $story
     */
    public function __construct(Story $story)
    {
        $this->story = $story;
        $q = new Queries($story);
        $this->index = new DickotomicIndex();
        foreach ($q->getTimeMap() as $item) {
            $this->index->data[] = [$item->ut, [$item->latitude, $item->longitude]];
        }
    }

    public function addImageFile($filePath)
    {
        $urlPath = '/photo/' . $this->albumName . '/';
        $storagePath = __DIR__ . '/../../public';
        if (!file_exists($storagePath . $urlPath)) {
            mkdir($storagePath . $urlPath, 0755, true);
        }

        $name = basename($filePath, '.jpg');
        $urlName = $urlPath . $name;

        $imageResize = new ImageResize($filePath);
        $imageResize->resizeToLongSide(500)->save($storagePath . $urlName . '.sm.jpg');
        $imageResize->crop(60, 60)->save($storagePath . $urlName . '.th.jpg');

        copy($filePath, $storagePath . $urlName . '.jpg');

        $photo = new Photo();
        $photo->storyId = $this->story->id;
        $this->readExif($filePath, $photo);
        $this->setPosition($photo);
        $photo->urlName = $urlPath . $name;

        $photo->save();
    }

    public function readExif($filePath, Photo $photo)
    {
        $exif = exif_read_data($filePath);
        $photo->ut = strtotime($exif['DateTime']);
    }

    public function setPosition(Photo $photo)
    {
        if ($photo->ut) {
            $point = $this->index->find($photo->ut);
            $photo->latitude = $point[0];
            $photo->longitude = $point[1];
        }
    }

}