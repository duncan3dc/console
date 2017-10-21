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
     * @var boolean $lock Whether this command uses locking or not
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
    public function getLockPath()
    {
        $path = $this->getApplication()->getLockPath();
        $command = str_replace(":", "_", $this->getName());
        return $path . "/" . $command . ".lock";
    }


    /**
     * Attempt to lock this command.
     *
     * @param OutputInterface $output The output object to use for any error messages
     *
     * @return void
     */
    public function lock(OutputInterface $output)
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
        if (!$this->lock) {
            return;
        }

        flock($this->lock, LOCK_UN);
        fclose($this->lock);

        unlink($this->getLockPath());
    }


    /**
     * Set that this command should not use locking.
     *
     * @return static
     */
    protected function doNotLock()
    {
        $this->lock = false;
        return $this;
    }


    /**
     * {@inheritDoc}
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->startTime = time();

        $timer = new Timer;

        $return = parent::run($input, $output);

        $duration = $timer->getDuration();
        $output->inline("[" . $this->getName() . "] ");
        $output->inline("Time: " . $duration->format() . ", ");
        $output->out(sprintf("Memory: %4.2fmb", memory_get_peak_usage(true) / 1048576));

        return $return;
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
