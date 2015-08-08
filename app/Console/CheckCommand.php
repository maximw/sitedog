<?php

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCommand extends Command
{
    protected function configure()
    {
        $this->setName('app:check')
            ->setDescription('Sends the newsletter');
    }

    // TODO Set up PHP in CLI mode
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo 'test';
    }
}