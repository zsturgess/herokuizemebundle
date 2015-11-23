<?php

namespace ZacSturgess\HerokuizeMeBundle\Actor;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * CodebaseActor
 */
class CodebaseActor implements ActorInterface
{
    private $baseDir;
    
    public function __construct() {
        $this->baseDir = __DIR__ . '/../../../../';
    }
    
    public function run()
    {
        $fs = new Filesystem;
        
        if (!$fs->exists([
            $this->baseDir . '.git',
            $this->baseDir . '.gitignore'
        ])) {
            return '<error>Codebase does not appear to be tracked in git.</error> Ensure that the root of the git repository is the root of the Symfony application';
        } else {
            return true;
        }
    }
    
    public function fix($force = false)
    {
        $fs = new Filesystem;
        
        if (!$fs->exists($this->baseDir . '.git')) {
            $gitInit = new Process('git init ' . $this->baseDir);
            $gitInit->start();
            $gitInit->wait();
            
            if (!$gitInit->isSuccessful()) {
                echo $gitInit->getStopSignal();
                throw new \RuntimeException('Tried to run "git init" but failed. Git said: ' . $gitInit->getOutput());
            }
        }
        
        if (!$fs->exists($this->baseDir . '.gitignore')) {
            $fs->copy(__DIR__ . '/../Resources/templates/.gitignore', $this->baseDir . '.gitignore', $force);
        }
    }
    
    public function getSuccessMessage()
    {
        return '<info>Codebase appears to be tracked in git correctly.</info>';
    }
    
    public function get12FactorLink()
    {
        return 'http://12factor.net/codebase';
    }
}
