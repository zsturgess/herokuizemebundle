<?php

namespace ZacSturgess\HerokuizeMeBundle\Actor;

/**
 * ConfigActor
 * @todo the fix here is incorrect. Incenteev can stay, we need to check for a .php
 * containing getenv() as an import and if not copy our template env_parameters.php
 */
class ConfigActor extends BaseActor
{
    const PARAM_BUILDER_SCRIPT_NAME = 'Incenteev\\ParameterHandler\\ScriptHandler::buildParameters';
    
    public function run()
    {   
        if (!$this->fs->exists($this->baseDir . 'composer.json')) {
            return true;
        }
        
        $composerJson = json_decode(file_get_contents($this->baseDir . 'composer.json'));
        
        if (in_array(self::PARAM_BUILDER_SCRIPT_NAME, $composerJson->scripts->post-install-cmd)) {
            return '<error>Composer is set up to override environment variables.</error> A composer install is currently set up to build the parameters.yml file from the template parameters.yml.dist file, which will override any environment variables you set.';
        } else {
            return true;
        }
    }
    
    public function fix()
    {
        if (!$this->fs->exists($this->baseDir . 'composer.json')) {
            return;
        }
        
        $composerJson = json_decode(file_get_contents($this->baseDir . 'composer.json'));
        $parameterBuilder = array_search(self::PARAM_BUILDER_SCRIPT_NAME, $composerJson->scripts->post-install-cmd);
        
        if ($parameterBuilder !== false) {
            unset($composerJson->scripts->{post-install-cmd}[$parameterBuilder]);
            file_put_contents($this->baseDir . 'composer.json', json_encode($composerJson, JSON_PRETTY_PRINT));
        }
    }
    
    public function getSuccessMessage()
    {
        return 'Project is set up for configuration via environment variables correctly.';
    }
    
    public function getInfoLink()
    {
        return 'http://12factor.net/config';
    }
}
