<?php

namespace KayStrobach\PageTreeFixer\Command;

use KayStrobach\PageTreeFixer\Service\FixOrphanedPagesService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixOrphanedPagesCommand extends Command
{
    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('checks if the pages have a rootline, if not mark them as deleted');
        $this->setHelp('');
    }

    /**
     * Executes the command for showing sys_log entries
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $service = new FixOrphanedPagesService($output);
        $service->run();
        return self::SUCCESS;
    }
}
