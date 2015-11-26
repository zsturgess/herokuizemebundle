<?php

namespace ZacSturgess\HerokuizeMeBundle\Helper;

use Symfony\Component\Process\ExecutableFinder;

/**
 * ComposerFinder
 */
class ComposerFinder {
    public static function find()
    {
        $composer = new ExecutableFinder;
        $composerPath = $composer->find('composer');
        
        if ($composerPath !== null) {
            return $composerPath;
        }
        
        $composerPath = $composer->find('composer.phar', null, [
            $this->baseDir,
            $this->baseDir . '/bin/'
        ]);
        
        if ($composerPath !== null) {
            return $composerPath;
        }
        
        throw new \RuntimeException('Cannot find a local installation of composer. Please ensure that composer is in your $PATH, or there is a composer.phar at the root of the symfony installation.');
    }
}
