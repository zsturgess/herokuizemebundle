<?php

namespace ZacSturgess\HerokuizeMeBundle\Actor;

/**
 * BuildReleaseRunActor
 */
class BuildReleaseRunActor extends BaseActor
{
    public function run()
    {   
        // @todo: Check for the existance of a valid procfile
    }
    
    public function fix()
    {
        // @todo: Copy a procfile into the root dir
    }
    
    public function getSuccessMessage()
    {
        return 'Project declares process types correctly.';
    }
    
    public function getInfoLink()
    {
        return 'http://12factor.net/build-release-run';
    }
}
