<?php

namespace ZacSturgess\HerokuizeMeBundle\Actor;

use Symfony\Component\Process\ExecutableFinder;

/**
 * DependanciesActor
 */
class DependanciesActor extends BaseActor
{
    private $composerPath = null;
    
    public function run()
    {
        //Does composer.json, composer.lock, vendor/ exist?
        if (!$this->fs->exists([
            $this->baseDir . 'composer.json',
            $this->baseDir . 'composer.lock',
            $this->baseDir . 'vendor/'
        ])) {
            return '<error>Dependancies are not being managed by composer correctly.</error> Ensure that composer.json, composer.lock and the vendor/ folder exist at the root of the symfony installation.';
        }
        
        //Are they ignored correctly?
        $gitIgnore = file_get_contents($this->baseDir . '.gitignore');
        if ($gitIgnore !== false) {
            if (strstr($gitIgnore, 'composer.json') !== false) {
                return '<error>Dependancies are not being managed by composer correctly.</error> composer.json appears to be ignored.';
            }
            if (strstr($gitIgnore, 'composer.lock') !== false) {
                return '<error>Dependancies are not being managed by composer correctly.</error> composer.lock appears to be ignored.';
            }
            if (strstr($gitIgnore, 'vendor') === false) {
                return '<error>Dependancies are not being managed by composer correctly.</error> The vendor/ folder should be ignored.';
            }
        }
        
        //Scan project for possible uses of functions in extenstions and check for their declaration in composer.json
        $dependencyExtension = $this->checkExtensions();
        if ($dependencyExtension !== false) {
            return '<error>Your project may have implicit dependencies on PHP extensions</error> We detected use of the ' . $dependencyExtension . ' extension, but "ext-' . $dependencyExtension . '" is not defined as a dependency in composer.json';
        }
    }
    
    public function fix()
    {
        //If composer.json doesn't exist, fail.
        if (!$this->fs->exists($this->baseDir . 'composer.json')) {
            throw new \RuntimeException('composer.json does not exist, so could not apply any autofixes. Run ' . $this->findComposer() . ' init at the root of your symfony installation and follow the on-screen instructions before trying to herokuize again.');
        }
        
        //If either of the other two do not exist, run a composer update
        if (!$this->fs->exists([
            $this->baseDir . 'composer.lock',
            $this->baseDir . 'vendor/'
        ])) {
            $composerInstall = $this->runCommand($this->findComposer() . ' install');
            
            if (!$composerInstall->isSuccessful()) {
                throw new \RuntimeException('Tried to run "composer install" but failed. Composer said: ' . $composerInstall->getOutput());
            }
        }
        
        //Add/remove from .gitignore as desired
        $gitIgnore = file($this->baseDir . '.gitignore');
        if ($gitIgnore !== false) {
            foreach ($gitIgnore as $lineNumber => $line) {
                if (
                    (strstr($line, 'composer.json') !== false) ||
                    (strstr($line, 'composer.lock') !== false)
                ) {
                    unset($file[$lineNumber]);
                }
            }
            
            file_put_contents($this->baseDir . '.gitignore', implode(PHP_EOL, $file));
            
            $gitIgnore = file_get_contents($this->baseDir . '.gitignore');
            if (strstr($gitIgnore, 'vendor') === false) {
                $gitIgnore .= PHP_EOL . '/vendor/';
                file_put_contents($this->baseDir . '.gitignore', $gitIgnore);
            }
        }
        
        //Add required exts via composer require
        while (($dependencyExtension = $this->checkExtensions()) !== false) {
            if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
                return '<error>Implicit dependancies on extensions cannot be checked on Windows.</error> Please see https://devcenter.heroku.com/articles/php-support#extensions';
            }
            
            $composerRequire = $this->runCommand($this->findComposer() . ' require ext-' . $dependencyExtension . ':*');
            
            if (!$composerRequire->isSuccessful()) {
                throw new \RuntimeException('Tried to require ext-' . $dependencyExtension . '. Composer said: ' . $composerRequire->getOutput());
            }
        }
    }
    
    public function getSuccessMessage()
    {
        return 'Dependancies appear to be being managed correctly.';
    }
    
    public function getInfoLink()
    {
        return 'http://12factor.net/dependencies';
    }
    
    private function findComposer() {
        if ($this->composerPath !== null) {
            return $this->composerPath;
        }
        
        $composer = new ExecutableFinder;
        $this->composerPath = $composer->find('composer');
        
        if ($this->composerPath !== null) {
            return $this->composerPath;
        }
        
        $this->composerPath = $composer->find('composer.phar', null, [
            $this->baseDir,
            $this->baseDir . '/bin/'
        ]);
        
        if ($this->composerPath !== null) {
            return $this->composerPath;
        }
        
        throw new \RuntimeException('Cannot find a local installation of composer. Please ensure that composer is in your $PATH, or there is a composer.phar at the root of the symfony installation.');
    }
    
    private function checkForExtensionUse($ext) {
        if (!extension_loaded($ext)) {
            return false;
        }
        
        if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
            // We're on windows. Whilst I could not use grep and wc -l, any
            // alternative would be very slow. Simply prompting the user to check
            // given the extension is loaded is acceptable.
            return true;
        }
        
        $funcs = get_extension_funcs($ext);
        foreach ($funcs as $func) {
            $process = $this->runCommand(sprintf(
                'grep -ri "%s(" %ssrc %svendor | wc -l',
                $func,
                $this->baseDir,
                $this->baseDir
            ));
            
            if ($process->getOutput() !== "0") {
                return true;
            }
        }
        
        return false;
    }
    
    private function checkExtensions() {
        $extensions = [
            'bcmath', 'calendar', 'exif', 'ftp', 'gd', 'gettext', 'intl', 'mbstring',
            'mcrypt', 'mysql', 'pcntl', 'shmop', 'soap', 'sqlite3', 'pdo_sqlite',
            'xmlrpc', 'xsl', 'apcu', 'blackfire', 'imagick', 'memcached', 'mongo',
            'newrelic', 'oauth', 'redis'
        ];
        
        $composerJson = file_get_contents($this->baseDir . 'composer.json');
        
        if ($composerJson === false) {
            return false;
        }
        
        foreach ($extensions as $extension) {
            if (strstr($composerJson, 'ext-' . $extension) !== false) {
                continue;
            }
            
            if ($this->checkForExtensionUse($extension) === true) {
                return $extension;
            }
        }
        
        return false;
    }
}
