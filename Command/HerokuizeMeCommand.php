<?php

namespace ZacSturgess\HerokuizeMeBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;
use ZacSturgess\HerokuizeMeBundle\Helper\ComposerFinder;
use ZacSturgess\HerokuizeMeBundle\Helper\ExtensionDependancyHelper;

/**
 * HerokuizeMeCommand
 */
class HerokuizeMeCommand extends Command
{
    private $changesMade = false;
    
    protected function configure()
    {
        $this
            ->setName('herokuize:me')
            ->setDescription('Installs the deployment hook and scans for implicit dependancies on PHP extensions')
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Installing deploy hook...</comment>');
        $this->installHook($output);
        $output->writeln('<info>OK</info>');
        
        $output->writeln('<comment>Scanning for implicit dependancies on PHP extensions...</comment>');
        $this->scanDependancies($output);
        $output->writeln('<info>OK</info>');
        
        $output->writeln('<info>Task complete.</info>');
        if ($this->changesMade) {
            $output->writeln('<comment>Don\'t forget to commit any changes to your composer.json and composer.lock files before pushing to Heroku.</comment>');
        }
    }
    
    private function installHook(OutputInterface $output)
    {
        $composerJson = json_decode(file_get_contents('composer.json'));
        
        if (!isset($composerJson->scripts)) {
            $composerJson->scripts = new \stdClass;
        }
        
        if (!isset($composerJson->scripts->{'post-install-cmd'})) {
            $composerJson->scripts->{'post-install-cmd'} = [];
        }
        
        if (in_array('ZacSturgess\HerokuizeMeBundle\ScriptHandler::herokuCompiler', $composerJson->scripts->{'post-install-cmd'})) {
            return;
        }
        
        $composerJson->scripts->{'post-install-cmd'}[] = 'ZacSturgess\HerokuizeMeBundle\ScriptHandler::herokuCompiler';
        
        file_put_contents(
            'composer.json',
            str_replace(
                '"_empty_"',
                '""',
                json_encode(
                    $composerJson,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                )
            )
        );
        
        $this->changesMade = true;
    }
    
    private function scanDependancies(OutputInterface $output)
    {
        $helper = new ExtensionDependancyHelper();
        $exts = $helper->checkExtensions();
        
        if (count($exts) > 0) {
            $output->writeln('<error>Found implicit dependancies on PHP extensions.</error> Fixing... ');
            
            foreach ($exts as $ext) {
                $output->write(' > Declaring dependancy on the <comment>' . $ext . '</comment> extension');
                
                $this->requireExtension($ext);
            }
            
            $this->changesMade = true;
        } else if (extension_loaded('mbstring')) {
            $output->writeln('The mbstring extension is loaded locally, but not explicitly used or required by your application. Symfony performance can be improved with the mbstring extension, so it will be added as an explicit dependancy such that it is loaded on Heroku.');
            
            try {
                $this->requireExtension('mbstring');
            } catch (\RuntimeException $ex) {
                $output->writeln('<comment>Failed to declare dependancy on mbstring. Your application should run fine without it, so you can ignore this.</comment>');
                $output->writeln('Caused by: ' . $ex->getMessage());
            }
            
            $this->changesMade = true;
        }
    }
    
    private function requireExtension($ext) {
        $builder = new ProcessBuilder([
            ComposerFinder::find(),
            'require',
            'ext-' . $ext,
            '*',
            '--no-interaction'
        ]);

        $process = $builder->getProcess();
        $process->run();
        $process->wait();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getCommandLine() . ' failed to run. Composer said: ' . $process->getOutput());
        }
    }
}
