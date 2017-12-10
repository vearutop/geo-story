<?php

namespace GeoTool\Command;

use GeoTool\Entities\User;
use Yaoi\Command;
use Yaoi\Command\Definition;

class AddUser extends Command
{
    public $login;
    public $name;

    static function setUpDefinition(Definition $definition, $options)
    {
        $options->login = Command\Option::create()->setType()->setIsUnnamed()->setIsRequired();
        $options->name = Command\Option::create()->setType()->setIsUnnamed()->setIsRequired();
    }

    public function performAction()
    {
        $user = new User();
        $user->login = $this->login;
        $user->name = $this->name;
        $user->save();
        $this->response->success('User ID ' . $user->id . ' created');
    }
}