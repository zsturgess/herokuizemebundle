<?php

namespace ZacSturgess\HerokuizeMeBundle;

use Composer\Script\Event;
use Symfony\Component\Process\ProcessBuilder;

/**
 * ScriptHandler
 */
class ScriptHandler {
    public static function herokuCompiler(Event $event)
    {
        if (getenv('STACK') === false) {
            return;
        }
        
        if (self::checkForLock()) {
            return;
        }
        
        $event->getIO()->write('Heroku deploy detected');
        
        if (getenv('SYMFONY_ENV') === 'prod' || getenv('SYMFONY__ENV') === 'prod') {
            return self::herokuProdCompiler($event);
        } else {
            return self::herokuDevCompiler($event);
        }
    }
    
    public static function herokuProdCompiler(Event $event)
    {
        self::parameterRemover($event);
        self::logRewriter($event);
        self::sessionFixer($event);
        self::extensionResolver($event);
    }
    
    public static function herokuDevCompiler(Event $event)
    {
        $event->getIO()->write('');
        $event->getIO()->write('! WARNING');
        $event->getIO()->write('   Symfony applications in dev mode are often subject to poor performance on Heroku.');
        $event->getIO()->write('   It\'s recommended that you run \'heroku config:set SYMFONY_ENV=prod\' locally.');
        $event->getIO()->write('');
        
        self::installDevDependancies($event);
        self::rewriteHtaccess($event);
        self::herokuProdCompiler($event);
    }

    public static function parameterRemover(Event $event)
    {
        // We must remove parameters.yml created by Incenteev to allow Symfony to pick up env vars.
        
        $event->getIO()->write('-----> Configuring Symfony to read parameters from config vars...');

        foreach (glob("app/config/*.yml") as $filename) {
            file_put_contents(
                $filename,
                str_replace(
                    '- { resource: parameters.yml }',
                    '',
                    file_get_contents($filename)
                )
            );
        }

        file_put_contents('app/config/parameters.yml', '');
    }
    
    public static function logRewriter(Event $event)
    {
        $event->getIO()->write('-----> Rerouting logs to event streams...');
        
        foreach (glob("app/config/*.yml") as $filename) {
            file_put_contents(
                $filename,
                str_replace(
                    '%kernel.logs_dir%/%kernel.environment%.log',
                    'php://stderr',
                    file_get_contents($filename)
                )
            );
        }
    }
    
    public static function sessionFixer(Event $event)
    {
        $event->getIO()->write('-----> Putting sessions into persistant storage...');
        
        if (getenv('SYMFONY__DATABASE_HOST') === false) {
            $event->getIO()->write('       WARNING: No database connection found (no SYMFONY__DATABASE_HOST environment variable seen) so skipping this step');
            return;
        }
        
        $contents = file_get_contents(__DIR__ . '/Resources/templates/pdo_sessions.yml');
        
        if (getenv('SYMFONY__DATABASE_PORT') === false) {
            $contents = str_replace(
                '%database_port%',
                '3306',
                $contents
            );
        }
        
        file_put_contents(
            'app/config/services.yml',
            $contents,
            FILE_APPEND
        );
        
        file_put_contents(
            'app/config/config.yml',
            preg_replace(
                '/^(\s+)handler_id:(\s+)~/m',
                '$1handler_id:$2session.handler.pdo',
                file_get_contents('app/config/config.yml')
            )
        );
    }
    
    public static function extensionResolver(Event $event)
    {
        $helper = new Helper\ExtensionDependancyHelper();
                
        if ($helper->hasExplicitDependanciesOnExtensions() !== false) {
            // Skip this intensive, slow check if we already have explicit dependancies on PHP extensions
            return;
        }
        
        $event->getIO()->write('-----> Scanning and declaring implicit dependencies on PHP extensions...');
        $exts = $helper->checkExtensions();
        
        if (count($exts) > 0) {
            $event->getIO()->write('       WARNING: Implicit dependancies found on the following PHP extensions:');
            
            foreach ($exts as $ext) {
                $event->getIO()->write('        - ' . $ext);
            }
            
            $event->getIO()->write('       You should run "composer require ext-<extension-name> \'*\'" locally and commit the result.');
        } else {
            $event->getIO()->write('       No implicit dependancies found. You can speed up the deployment of this app and improve Symfony\'s performance by running "composer require ext-mbstring \'*\'" locally and committing the result.');
        }
    }
    
    public static function installDevDependancies(Event $event)
    {
        $event->getIO()->write('-----> Installing dev dependencies...');
        
        $builder = new ProcessBuilder([
            'composer',
            'install',
            '--dev',
            '--prefer-dist',
            '--optimize-autoloader',
            '--no-interaction'
        ]);
        
        $process = $builder->getProcess();
        $process->setTimeout(60 * 60);
        $process->run();
        $process->wait();
        
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getCommandLine() . ' failed to run. Composer said: ' . $process->getOutput());
        }
    }
    
    public static function rewriteHtaccess(Event $event) {
        $event->getIO()->write('-----> Rewriting requests to app_dev.php...');
        $filename = 'web/.htaccess';

        $content = str_replace(
            'app.php',
            'app_dev.php',
            file_get_contents($filename)
        );
        
        file_put_contents(
            $filename,
            str_replace(
                'app\.php',
                'app_dev\.php',
                $content
            )
        );
    }
    
    private static function checkForLock() {
        $lockFile = self::determineCacheDir() . 'herokuize.lock';
        
        if (file_exists($lockFile)) {
            return true;
        }
        
        file_put_contents($lockFile, time());
        return false;
    }
    
    private static function determineCacheDir() {
        if (is_dir('app/cache')) {
            return 'app/cache/';
        } else if (is_dir('var/cache')) {
            return 'var/cache/';
        } else {
            throw new \RuntimeException('Could not find the Symfony cache directory in app/ or var/');
        }
    }
}
