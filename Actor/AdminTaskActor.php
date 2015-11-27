<?php

namespace ZacSturgess\HerokuizeMeBundle\Actor;

use ZacSturgess\HerokuizeMeBundle\Helper\ComposerFinder;

/**
 * AdminTaskActor
 */
class AdminTaskActor extends BaseActor
{
    private $composerPath = null;
    
    public function run()
    {   
        // Check dev dependancies are loaded conditionally
        $removeDevDeps = $this->runCommand($this->findComposer() . ' install --no-dev');
        $replaceDevDeps = $this->runCommand($this->findComposer() . ' install');
        
        if (!$replaceDevDeps->isSuccessful()) {
            throw new \RuntimeException('Could not run "composer install" to restore require-dev packages. Composer said: ' . $replaceDevDeps->getOutput());
        }
        
        if (!$removeDevDeps->isSuccessful()) {
            return '<error>Symfony is trying to load dev dependencies even when they don\'t exist.</error> Heroku does not install require-dev packages, but Symfony is expecting them to exist when using the CLI.';
        }
        
        return true;
    }
    
    public function fix()
    {
        $patchedAppKernel = str_replace(
            '    public function registerBundles()',
            file_get_contents($this->templateDir . 'AppKernel_conditional_load.php'),
            file_get_contents($this->baseDir . 'app/AppKernel.php')
        );
        
        file_put_contents(
            $this->baseDir . 'app/AppKernel.php',
            preg_replace(
                '/^([\s]+)\$bundles\[\] = new ([\S]+)\(\);$/m',
                '${1}\$this->registerBundleIfExists(\'${2}\', \$bundles);',
                $patchedAppKernel
            )
        );
    }
    
    public function getSuccessMessage()
    {
        return 'Project correctly loads dev dependancies only if they exist.';
    }
    
    public function getInfoLink()
    {
        return 'http://12factor.net/admin-processes';
    }
    
    private function findComposer() {
        if ($this->composerPath === null) {
            $this->composerPath = ComposerFinder::find();
        }
        
        return $this->composerPath;
    }
}
