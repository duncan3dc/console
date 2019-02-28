<?php

namespace Category;

use duncan3dc\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OutputStuffCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write("Some ");
        $output->writeln("content");
    }
}
