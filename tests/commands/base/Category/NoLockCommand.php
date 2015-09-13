<?php

namespace Category;

use duncan3dc\Console\Command;

class NoLockCommand extends Command
{
    protected function configure()
    {
        $this->doNotLock();
    }
}
