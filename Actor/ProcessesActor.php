<?php

namespace ZacSturgess\HerokuizeMeBundle\Actor;

/**
 * ProcessesActor
 */
class ProcessesActor extends BaseActor
{
    public function run()
    {   
        // Check session config is not for local files
        $config = $this->parser->parse(file_get_contents($this->baseDir . 'app/config/config.yml'));
        
        if ($config['framework']['session']['handler_id'] === null) {
            return '<error>Symfony is configured to use the default session handler.</error> Sessions will be stored as files, which doesn\'t work on disposable servers like Heroku dynos.';
        }
        
        return true;
    }
    
    public function fix()
    {
        // Fix config.yml
        file_put_contents(
            $this->baseDir . 'app/config/config.yml',
            preg_replace(
                '/handler_id:.*~/',
                'handler_id: session.handler.pdo',
                file_get_contents($this->baseDir . 'app/config/config.yml')
            )
        );
        
        // Fix parameters.yml
        file_put_contents(
            $this->baseDir . 'app/config/parameters.yml',
            preg_replace(
                '/^(\s+database_port:\s+)[^0-9]+/m',
                '${1}3306',
                file_get_contents($this->baseDir . 'app/config/parameters.yml')
            )
        );
        
        // Add service definition
        if (!$this->fs->exists($this->baseDir . 'app/config/services.yml')) {
            file_put_contents($this->baseDir . 'app/config/services.yml', 'services:');
        }
        
        file_put_contents(
            $this->baseDir . 'app/config/services.yml',
            file_get_contents($this->templateDir . 'pdo_sessions.yml'),
            FILE_APPEND
        );
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
