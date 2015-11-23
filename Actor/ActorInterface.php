<?php

namespace ZacSturgess\HerokuizeMeBundle\Actor;

/**
 * ActorInterface
 */
interface ActorInterface
{
    public function run();
    public function fix($force = false);
    
    public function get12FactorLink();
    public function getSuccessMessage();
}
