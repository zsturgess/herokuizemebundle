<?php

namespace ZacSturgess\HerokuizeMeBundle\Actor;

/**
 * BuildReleaseRunActor
 */
class BuildReleaseRunActor extends BaseActor
{
    public function run()
    {
        if (!$this->fs->exists($this->baseDir . 'Procfile')) {
            return '<error>No Procfile exists at the root of the Symfony installation.</error> When Heroku tries to run your app, the root directory instead of the web/ folder will be served.';
        }
        
        if (strpos('web:', 'web:') === false) {
            return '<error>Your Procfile does not declare a "web" process type.</error> When Heroku tries to run your app, the root directory instead of the web/ folder will be served.';
        }
        
        return true;
    }
    
    public function fix()
    {
        // Copy a procfile into the root dir
        file_put_contents(
            $this->baseDir . 'Procfile',
            file_get_contents($this->templateDir . 'Procfile'),
            FILE_APPEND
        );
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
