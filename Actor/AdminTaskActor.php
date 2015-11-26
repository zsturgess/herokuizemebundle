<?php

namespace ZacSturgess\HerokuizeMeBundle\Actor;

/**
 * AdminTaskActor
 */
class AdminTaskActor extends BaseActor
{
    public function run()
    {   
        // @todo: Check dev dependancies are loaded conditionally
    }
    
    public function fix()
    {
        // @todo: Fix AppKernel
    }
    
    public function getSuccessMessage()
    {
        return 'Project correctly loads dev dependancies only if they exist.';
    }
    
    public function getInfoLink()
    {
        return 'http://12factor.net/admin-processes';
    }
}
