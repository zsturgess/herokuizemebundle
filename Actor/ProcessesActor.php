<?php

namespace ZacSturgess\HerokuizeMeBundle\Actor;

/**
 * ProcessesActor
 */
class ProcessesActor extends BaseActor
{
    public function run()
    {   
        // @todo: Check session config is not for local files
    }
    
    public function fix()
    {
        // @todo: Fix config
    }
    
    public function getSuccessMessage()
    {
        return 'Project does not rely on local sessions correctly.';
    }
    
    public function getInfoLink()
    {
        return 'http://12factor.net/processes';
    }
}
