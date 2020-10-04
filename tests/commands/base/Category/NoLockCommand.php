<?php

namespace Category;

use duncan3dc\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NoLockCommand extends Command
{
    protected function configure(): void
    {
        $this->doNotLock();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        return 0;
    }
}
