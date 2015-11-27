<?php

namespace ZacSturgess\HerokuizeMeBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ZacSturgess\HerokuizeMeBundle\Actor;

/**
 * HerokuizeMeCommand
 */
class HerokuizeMeCommand extends Command
{
    private $actors;
    
    protected function configure()
    {
        $this
            ->setName('herokuize:me')
            ->setDescription('')
            ->addOption(
               'auto-fix',
               'a',
               InputOption::VALUE_NONE,
               'If set, the task will attempt to auto-fix any issues it finds'
            )
        ;
    }
    
    protected function initialize(InputInterface $input, OutputInterface $output) {
        $baseDir = $this->getApplication()->getKernel()->getRootDir() . '/../';
        
        $this->actors = [
            new Actor\CodebaseActor($baseDir),
            new Actor\DependenciesActor($baseDir),
            new Actor\ConfigActor($baseDir),
            new Actor\BackingServicesActor($baseDir),
            new Actor\BuildReleaseRunActor($baseDir),
            new Actor\ProcessesActor($baseDir),
            new Actor\LogActor($baseDir),
            new Actor\AdminTaskActor($baseDir),
        ];
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->actors as $actor) {
            $output->writeln('');
            $output->writeln('Testing against ' . $actor->getInfoLink());
            
            $result = $actor->run();
            
            if ($result === true) {
                $output->writeln('  <info>' . $actor->getSuccessMessage() . '</info>');
            } else {
                $output->writeln('  ' . $result);
                
                if ($input->getOption('auto-fix')) {
                    $return = $actor->fix();
                    
                    if (empty($return)) {
                        $output->writeln('  <info>Auto-fix applied.</info>');
                    } else {
                        $output->writeln('  ' . $return);
                    }
                }
            }
        }
        
        $output->writeln('');
        $output->writeln('Task complete.');
        
        if (!$input->getOption('auto-fix')) {
            $output->writeln('<comment>No auto-fixes have been applied. Run again with</comment> -a <comment>or</comment> --auto-fix <comment>to apply patches.</comment>');
        } else {
            $output->writeln('<info>Auto-fixes applied.</info> <comment>Check the changes made by running</comment> git diff <comment>then commit them by running</comment>  git commit -am\'Herokuize Me\'');
        }
    }
}
