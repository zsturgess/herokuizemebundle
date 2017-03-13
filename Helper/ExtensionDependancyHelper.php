<?php

namespace ZacSturgess\HerokuizeMeBundle\Helper;

use Symfony\Component\Process\Process;

/**
 * ExtensionDependancyHelper
 */
class ExtensionDependancyHelper {
    private function checkForExtensionUse($ext) {
        $funcs = get_extension_funcs($ext);
        
        if (!is_array($funcs)) {
            return false;
        }
        
        foreach ($funcs as $func) {
            $process = $this->runCommand(sprintf(
                'grep -ri "%s(" src | wc -l',
                $func
            ));
            
            if (substr($process->getOutput(), 0, 1) !== "0") {
                return true;
            }
        }
        
        return false;
    }
    
    public function checkExtensions() {
        $extensions = [
            'bcmath', 'calendar', 'exif', 'ftp', 'gd', 'gettext', 'intl', 'mbstring',
            'mcrypt', 'mysql', 'pcntl', 'shmop', 'soap', 'sqlite3', 'pdo_sqlite',
            'xmlrpc', 'xsl', 'apcu', 'blackfire', 'imagick', 'memcached', 'mongo',
            'newrelic', 'oauth', 'redis'
        ];
        
        $implicitDependancies = [];
        
        $composerJson = file_get_contents('composer.json');
        
        if ($composerJson === false) {
            throw new \RuntimeException('Could not read from composer.json');
        }
        
        foreach ($extensions as $extension) {
            if (!extension_loaded($extension)) {
                continue;
            }
            
            if (strstr($composerJson, 'ext-' . $extension) !== false) {
                continue;
            }
            
            if ($this->checkForExtensionUse($extension) === true) {
                $implicitDependancies[] = $extension;
            }
        }
        
        return $implicitDependancies;
    }
    
    public function hasExplicitDependanciesOnExtensions() {
        $composerJson = file_get_contents('composer.json');
        
        return (strstr($composerJson, 'ext-'));
    }
    
    private function runCommand($cmd) {
        $process = new Process($cmd);
        $process->run();
        $process->wait();
        
        return $process;
    }
}
