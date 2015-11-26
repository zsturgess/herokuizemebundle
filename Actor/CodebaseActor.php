<?php

namespace ZacSturgess\HerokuizeMeBundle\Actor;

/**
 * CodebaseActor
 */
class CodebaseActor extends BaseActor
{   
    public function run()
    {   
        if (!$this->fs->exists([
            $this->baseDir . '.git',
            $this->baseDir . '.gitignore'
        ])) {
            return '<error>Codebase does not appear to be tracked in git.</error> Ensure that the root of the git repository is the root of the Symfony application';
        } else {
            return true;
        }
    }
    
    public function fix()
    {
       if (!$this->fs->exists($this->baseDir . '.git')) {
            $gitInit = $this->runCommand('git init ' . $this->baseDir);
            
            if (!$gitInit->isSuccessful()) {
                throw new \RuntimeException('Tried to run "git init" but failed. Git said: ' . $gitInit->getOutput());
            }
        }
        
        if (!$this->fs->exists($this->baseDir . '.gitignore')) {
            $this->fs->copy(__DIR__ . '/../Resources/templates/.gitignore', $this->baseDir . '.gitignore', $force);
        }
    }
    
    public function getSuccessMessage()
    {
        return 'Codebase appears to be tracked in git correctly.';
    }
    
    public function getInfoLink()
    {
        return 'http://12factor.net/codebase';
    }
}
