<?php

namespace ZacSturgess\HerokuizeMeBundle\Actor;

use ZacSturgess\HerokuizeMeBundle\Helper\ComposerFinder;

/**
 * BackingServicesActor
 */
class BackingServicesActor extends BaseActor
{
    private $dbConfigKeys = ['driver', 'host', 'port', 'dbname', 'user', 'password'];
    private $emailConfigKeys = ['transport', 'host', 'username', 'password'];
    
    protected $config;
    
    public function run($fix = false)
    {
        if ($this->config === null) {
            $this->config = $this->parser->parse(file_get_contents($this->baseDir . 'app/config/config.yml'));
        }
        
        foreach ($this->dbConfigKeys as $key) {
            if (!$this->isParameterized($this->config['doctrine']['dbal'][$key])) {
                if ($fix === true) {
                    $this->fixConfig('database_' . $key, $this->config['doctrine']['dbal'][$key]);
                } else {
                    return '<error>Backing services are hard-coded, not parameterized.</error> The config value for doctrine.dbal.' . $key . ' is not a parameter, so could not be overriden by environment variable configuration.';
                }
            }
        }
        
        foreach ($this->emailConfigKeys as $key) {
            if (!$this->isParameterized($this->config['swiftmailer'][$key])) {
                if ($fix === true) {
                    $this->fixConfig('mailer_' . $key, $this->config['swiftmailer'][$key]);
                } else {
                    return '<error>Backing services are hard-coded, not parameterized.</error> The config value for swiftmailer.' . $key . ' is not a parameter, so could not be overriden by environment variable configuration.';
                }
            }
        }
        
        return true;
    }
    
    public function fix()
    {
        $this->run(true);
        
        // Now run composer script to populate parameters.yml with the values,
        // so as not to break the local working copy
        $composer = ComposerFinder::find();
        $composerRun = $this->runCommand($composer . ' run-script post-update-cmd');
            
        if (!$composerRun->isSuccessful()) {
            throw new \RuntimeException('Tried to run "composer run-script post-update-cmd" but failed. Composer said: ' . $composerRun->getOutput());
        }
    }
    
    public function getSuccessMessage()
    {
        return 'Project is set up to connect to the database and email delivery as backing services via configuration correctly.';
    }
    
    public function getInfoLink()
    {
        return 'http://12factor.net/backing-services';
    }
    
    private function isParameterized($configValue)
    {
        return (
            (substr($configValue, 0, 1) === '%') && 
            (substr($configValue, -1, 1) === '%')
        );
    }
    
    private function fixConfig($key, $value)
    {
        //Put the current value in parameters.yml.dist
        file_put_contents(
            $this->baseDir . 'app/config/parameters.yml.dist',
            PHP_EOL . '    ' . str_pad($key . ':', 19) . $value,
            FILE_APPEND
        );
        
        // Replace value in config.yml with %key%
        file_put_contents(
            $this->baseDir . 'app/config/config.yml',
            str_replace(' ' . $value, " %$key%", file_get_contents($this->baseDir . 'app/config/config.yml'))
        );
    }
}
