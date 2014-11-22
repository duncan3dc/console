<?php

namespace duncan3dc\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * {@inheritDoc}
 */
class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var int $startTime The unix timestamp that the command started running
     */
    protected $startTime = null;


    /**
     * {@inheritDoc}
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->startTime = time();
        return parent::run($input, $output);
    }


    /**
     * Check if the passed time has elapsed since the command start.
     *
     * @param int $timeout The number of seconds the command is allowed to run for
     *
     * @return boolean True if the time has been exceded and the command should end
     */
    public function timeout($timeout)
    {
        # Check if the application is currently allowing time limiting or not
        if (!$this->getApplication()->timeLimit()) {
            return false;
        }

        $endTime = $this->startTime + $timeout;

        return (time() > $endTime);
    }
}
