<?php

namespace ZacSturgess\HerokuizeMeBundle\Actor;

use Symfony\Component\Yaml\Dumper;

/**
 * LogActor
 */
class LogActor extends BaseActor
{
    public function run()
    {   
        // Check logs go to stderr
        $prodConfig = $this->parser->parse(file_get_contents($this->baseDir . 'app/config/config_prod.yml'));
        
        $correctHandlers = 0;
        
        if (isset($prodConfig['monolog']) && isset($prodConfig['monolog']['handlers'])) {
            foreach ($prodConfig['monolog']['handlers'] as $handler) {
                if (
                    !isset($handler['path']) || 
                    (
                        $handler['path'] !== 'php://stderr' &&
                        strpos($handler['path'], '%') !== 0
                    )
                ) {
                    return '<error>Symfony is configured to always send logs to files.</error> This doesn\'t work on disposable servers like Heroku dynos.';
                } else {
                    $correctHandlers++;
                }
            }
        }
        
        if ($correctHandlers !== 0) {
            return true;
        }
        
        $config = $this->parser->parse(file_get_contents($this->baseDir . 'app/config/config.yml'));
        
        if (isset($config['monolog']) && isset($config['monolog']['handlers'])) {
            foreach ($config['monolog']['handlers'] as $handler) {
                if (
                    !isset($handler['path']) || 
                    (
                        $handler['path'] !== 'php://stderr' &&
                        strpos($handler['path'], '%') !== 0
                    )
                ) {
                    return '<error>Symfony is configured to always send logs to files.</error> This doesn\'t work on disposable servers like Heroku dynos.';
                }
            }
        }
        
        return true;
    }
    
    public function fix()
    {
        // Our env_paramters.php contains cusotm logic we depend on, so fail if we're not using it
        if (!$this->fs->exists($this->baseDir . 'app/config/env_parameters.php')) {
            return '<error>Could not apply auto-fixes without overwriting your own custom changes.</error> It\'s recommended that you redirect logs to php://stderr instead of a file when on Heroku.';
        }
        
        $dumper = new Dumper;
        
        foreach (['config.yml', 'config_prod.yml', 'config_dev.yml'] as $configFile) {
            $config = $this->parser->parse(file_get_contents($this->baseDir . 'app/config/' . $configFile));
        
            if (isset($config['monolog']) && isset($config['monolog']['handlers'])) {
                foreach ($config['monolog']['handlers'] as $handlerKey => $handler) {
                    if (
                        !isset($handler['path']) || 
                        (
                            $handler['path'] !== 'php://stderr' &&
                            strpos($handler['path'], '%') !== 0
                        )
                    ) {
                        $config['monolog']['handlers'][$handlerKey]['path'] = '%logging_location%';
                    }
                }
            }
            
            file_put_contents(
                $this->baseDir . 'app/config/' . $configFile,
                $dumper->dump($config, 4)
            );
        }
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
