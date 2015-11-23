<?php

namespace ZacSturgess\HerokuizeMeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Console\Application;
use ZacSturgess\HerokuizeMeBundle\Command\HerokuizeMeCommand;

class ZacSturgessHerokuizeMeBundle extends Bundle
{
    public function registerCommands(Application $app) {
        $app->add(new HerokuizeMeCommand());
    }
}
