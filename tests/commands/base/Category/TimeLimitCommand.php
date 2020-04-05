<?php

namespace Category;

use duncan3dc\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TimeLimitCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        for ($i = 0; $i < 5; $i++) {
            sleep(1);
            if ($this->timeout(2)) {
                break;
            }
        }
        return 0;
    }
}
