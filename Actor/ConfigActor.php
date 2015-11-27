<?php

namespace ZacSturgess\HerokuizeMeBundle\Actor;

use Symfony\Component\Yaml\Dumper;

/**
 * ConfigActor
 */
class ConfigActor extends BaseActor
{
    public function run()
    {   
        $haveSeenParameters = false;
        $config = $this->parser->parse(file_get_contents($this->baseDir . 'app/config/config.yml'));
        
        foreach ($config['imports'] as $import) {
            if ($haveSeenParameters === false) {
                if ($import['resource'] === 'parameters.yml') {
                    $haveSeenParameters = true;
                }
                
                continue;
            }
            
            if (substr($import['resource'], -4) === '.php') {
                $importFilename = $this->baseDir . 'app/config/' . $import['resource'];
                
                if (strpos(file_get_contents($importFilename), 'getenv(')) {
                    return true;
                }
            }
        }
        
        if ($haveSeenParameters === false) {
            return true;
        } else {
            return '<error>Symfony is configured to ignore environment variables.</error> No PHP script that makes a call to getenv() was imported after parameters.yml, so no parameters will be configurable via environment variables.';
        }
    }
    
    public function fix()
    {
        $dumper = new Dumper;
        
        $this->fs->copy($this->templateDir . 'env_parameters.php', $this->baseDir . 'app/config/env_parameters.php');
        
        $config = $this->parser->parse(file_get_contents($this->baseDir . 'app/config/config.yml'));
        $config['imports'][] = ['resource' => 'env_parameters.php'];
        
        file_put_contents(
            $this->baseDir . 'app/config/config.yml',
            $dumper->dump($config, 4)
        );
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
