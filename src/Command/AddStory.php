<?php

namespace GeoTool\Command;

use GeoTool\Entities\Story;
use GeoTool\Entities\User;
use Yaoi\Command;
use Yaoi\Command\Definition;

class AddStory extends Command
{
    public $login;
    public $name;
    public $title;

    public $timezone;
    public $timeFrom;
    public $timeTo;

    static function setUpDefinition(Definition $definition, $options)
    {
        $options->login = Command\Option::create()->setType()->setIsRequired();
        $options->name = Command\Option::create()->setType()->setIsRequired();
        $options->title = Command\Option::create()->setType();
        $options->timezone = Command\Option::create()->setType();
        $options->timeFrom = Command\Option::create()->setType();
        $options->timeTo = Command\Option::create()->setType();
    }

    public function performAction()
    {
        $user = new User();
        $user->login = $this->login;
        if (!$user = $user->findSaved()) {
            $this->response->error("Unable to find user by login: $this->login");
            return false;
        }

        $story = new Story();
        $story->userId = $user->id;
        $story->name = $this->name;
        $story->title = $this->title;

        if ($this->timezone) {
            if (false === date_default_timezone_set($this->timezone)) {
                $this->response->error("Bad timezone: $this->timezone");
                return false;
            }
            $story->timezone = $this->timezone;
        }

        if ($this->timeFrom) {
            $story->utFrom = strtotime($this->timeFrom);
        }
        if ($this->timeTo) {
            $story->utTo = strtotime($this->timeTo);
        }

        $story->save();
        $this->response->success("Story create with ID $story->id");
        return true;
    }


}