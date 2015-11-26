<?php

namespace ZacSturgess\HerokuizeMeBundle\Actor;

/**
 * LogActor
 */
class LogActor extends BaseActor
{
    public function run()
    {   
        // @todo: Check logs go to stderr at least in prod mode
    }
    
    public function fix()
    {
        // @todo: Fix config (perhaps in advanced way with php logic)
    }
    
    public function getSuccessMessage()
    {
        return 'Project correctly treats logs as event streams.';
    }
    
    public function getInfoLink()
    {
        return 'http://12factor.net/logs';
    }
}
