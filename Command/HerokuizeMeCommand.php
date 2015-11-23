<?php

namespace ZacSturgess\HerokuizeMeBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
               'force',
               'f',
               InputOption::VALUE_NONE,
               'If set, the task will overwrite files with the same name if they exist already'
            )
            ->addOption(
               'auto-fix',
               'a',
               InputOption::VALUE_NONE,
               'If set, the task will attempt to auto-fix any issues it finds'
            )
        ;
        
        $this->actors = [
            "CodebaseActor"
        ];
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->actors as $actorName) {
            $output->writeln('');
            
            $actor = $this->getActorByName($actorName);
            $output->writeln('Testing against ' . $actor->get12FactorLink());
            
            $result = $actor->run();
            
            if ($result === true) {
                $output->writeln('  ' . $actor->getSuccessMessage());
            } else {
                $output->writeln('  ' . $result);
                
                if ($input->getOption('auto-fix')) {
                    $actor->fix($input->getOption('force'));
                    $output->writeln('  <info>Auto-fix applied.</info>');
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
    
    private function getActorByName($actorName) {
        $actor = 'ZacSturgess\\HerokuizeMeBundle\\Actor\\' . $actorName;
        
        return new $actor();
    }
}
