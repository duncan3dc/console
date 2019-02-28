<?php

namespace duncan3dc\Console;

use duncan3dc\SymfonyCLImate\Output;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function is_resource;

/**
 * @inheritdoc
 */
class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var bool|resource $lock Whether this command uses locking or not
     */
    protected $lock = true;

    /**
     * @var int $startTime The unix timestamp that the command started running
     */
    protected $startTime = null;


    /**
     * Get the full path to the lock file for this command.
     *
     * @return string The path for the lock file
     */
    public function getLockPath(): string
    {
        $path = $this->getApplication()->getLockPath();
        $command = str_replace(":", "_", $this->getName());
        return $path . "/" . $command . ".lock";
    }


    /**
     * Attempt to lock this command.
     *
     * @param Output $output The output object to use for any error messages
     *
     * @return void
     */
    public function lock(Output $output)
    {
        # If this command doesn't require locking then don't do anything
        if (!$this->lock) {
            return;
        }

        $path = $this->getLockPath();

        # If the lock file doesn't already exist then we are creating it, so we need to open the permissions on it
        $newFile = !file_exists($path);

        # Attempt to create/open a lock file for this command
        if (!$this->lock = fopen($path, "w")) {
            $output->error("Unable to create a lock file (" . $path . ")");
            exit(Application::STATUS_PERMISSIONS);
        }

        # Attempt to lock the file we've just opened
        if (!flock($this->lock, LOCK_EX | LOCK_NB)) {
            fclose($this->lock);
            $output->error("Another instance of this command (" . $this->getName() . ") is currently running");
            exit(Application::STATUS_LOCKED);
        }

        # Ensure the permissions are as open as possible to allow multiple users to run the same command
        if ($newFile) {
            chmod($path, 0777);
        }
    }


    /**
     * Release a lock on the command (if one was acquired).
     *
     * @return void
     */
    public function unlock()
    {
        # If this command doesn't require locking then don't do anything
        if (!is_resource($this->lock)) {
            return;
        }

        flock($this->lock, LOCK_UN);
        fclose($this->lock);

        unlink($this->getLockPath());
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


    /**
     * @inheritdoc
     */
    public function run(InputInterface $input, OutputInterface $output)
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


    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->command($input, $output);
    }


    /**
     * Execute this command.
     *
     * @param InputInterface $input
     * @param Output $output
     *
     * @return int|null Zero or null if everything went fine, otherwise the error code
     */
    protected function command(InputInterface $input, Output $output)
    {
        return parent::execute($input, $output);
    }
}
