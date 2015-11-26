<?php

namespace ZacSturgess\HerokuizeMeBundle\Actor;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * BaseActor
 */
abstract class BaseActor
{
    protected $baseDir;
    protected $templateDir;
    protected $fs;
    
    public function __construct($baseDir)
    {
        $this->baseDir = $baseDir;
        $this->templateDir = __DIR__ . '/../Resources/templates/';
        $this->fs = new Filesystem;
    }
    
    /**
     * Run a command and wait for it
     * 
     * @param string $cmd
     * @return Process
     */
    protected function runCommand($cmd) {
        $process = new Process($cmd);
        $process->run();
        $process->wait();
        
        return $process;
    }
    
    abstract public function run();
    abstract public function fix();
    abstract public function getSuccessMessage();
    abstract public function getInfoLink();
}
