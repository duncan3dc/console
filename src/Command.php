<?php

namespace duncan3dc\Console;

use duncan3dc\SymfonyCLImate\Output;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockInterface;

class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var bool|LockInterface $lock Whether this command uses locking or not
     */
    protected $lock = true;

    /**
     * @var int|null $startTime The unix timestamp that the command started running
     */
    protected ?int $startTime = null;


    public function getName(): string
    {
        $name = parent::getName();

        if ($name === null) {
            throw new \BadMethodCallException("Unable to call getName() in this context");
        }

        return $name;
    }


    public function getApplication(): Application
    {
        $application = parent::getApplication();

        if (!$application instanceof Application) {
            throw new \BadMethodCallException("Unable to call getApplication() in this context");
        }

        return $application;
    }


    /**
     * Attempt to lock this command.
     */
    public function lock(Output $output): void
    {
        # If this command doesn't require locking then don't do anything
        if (!$this->lock) {
            return;
        }

        $this->lock = $this->getApplication()->getLockFactory()->createLock($this->getName());

        if (!$this->lock->acquire()) {
            $output->error("Another instance of this command (" . $this->getName() . ") is currently running");
            exit(Application::STATUS_LOCKED);
        }
    }


    /**
     * Release a lock on the command (if one was acquired).
     */
    public function unlock(): void
    {
        if ($this->lock instanceof LockInterface) {
            $this->lock->release();
        }
    }


    /**
     * Set that this command should not use locking.
     *
     * @return $this
     */
    protected function doNotLock(): Command
    {
        $this->lock = false;
        return $this;
    }


    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->startTime = time();

        $timer = new Timer();

        $return = parent::run($input, $output);

        $application = $this->getApplication();
        if ($application instanceof Application && $application->showResourceInfo()) {
            $duration = $timer->getDuration();
            $memory = memory_get_peak_usage(true) / 1048576;
            $output->write("[" . $this->getName() . "] ");
            $output->write("Time: " . $duration->format() . ", ");
            $output->writeln(sprintf("Memory: %4.2fmb", $memory));
        }

        return $return;
    }


    /**
     * Check if the passed time has elapsed since the command start.
     *
     * @param int $timeout The number of seconds the command is allowed to run for
     *
     * @return bool True if the time has been exceeded and the command should end
     */
    public function timeout(int $timeout): bool
    {
        $application = $this->getApplication();
        if (!$application instanceof Application) {
            return false;
        }

        # Check if the application is currently allowing time limiting or not
        if (!$application->timeLimit()) {
            return false;
        }

        $endTime = $this->startTime + $timeout;

        return (time() > $endTime);
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->command($input, $output);
    }


    /**
     * Execute this command.
     *
     * @return int Zero if everything went fine, otherwise the error code
     */
    protected function command(InputInterface $input, Output $output): int
    {
        return parent::execute($input, $output);
    }
}
