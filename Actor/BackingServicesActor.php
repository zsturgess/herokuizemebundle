<?php

namespace ZacSturgess\HerokuizeMeBundle\Actor;

/**
 * BackingServicesActor
 */
class BackingServicesActor extends BaseActor
{
    public function run()
    {   
        // @todo: Check DB, Emails, etc. declares config via parameters
    }
    
    public function fix()
    {
        // @todo: move any hardcoded config into parameters.yml.dist, replace config with parameter, run composer update
    }
    
    public function getSuccessMessage()
    {
        return 'Project is set up to connect to backing services via configuration correctly.';
    }
    
    public function getInfoLink()
    {
        return 'http://12factor.net/backing-services';
    }
}
