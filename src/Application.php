<?php

namespace duncan3dc\Console;

use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * {@inheritDoc}
 */
class Application extends \Symfony\Component\Console\Application
{
    /**
     * The return code used when a command cannot run as it is locked
     */
    const STATUS_LOCKED = 201;

    /**
     * The return code used when a permissions issue prevented a command from running
     */
    const STATUS_PERMISSIONS = 202;

    /**
     * @var string $lockPath The directory to store command locks in
     */
    protected $lockPath = "/tmp/console-locks";

    /**
     * @var boolean $timeLimit Whether the application currently allows time limits for commands
     */
    protected $timeLimit = true;


    /**
     * {@inheritDoc}
     */
    public function __construct($name = false, $version = false)
    {
        parent::__construct($name, $version);

        $dispatcher = new EventDispatcher;
        $this->setDispatcher($dispatcher);

        # Attempt to acquire a unique lock on the command
        $dispatcher->addListener(ConsoleEvents::COMMAND, function(ConsoleCommandEvent $event) {
            $command = $event->getCommand();
            if (!is_subclass_of($command, __NAMESPACE__ . "\\Command")) {
                return;
            }
            $command->lock($event->getOutput());
        });

        # If we achieved a lock above then unlock it after the job finishes
        $dispatcher->addListener(ConsoleEvents::TERMINATE, function(ConsoleTerminateEvent $event) {
            $command = $event->getCommand();
            if (!is_subclass_of($command, __NAMESPACE__ . "\\Command")) {
                return;
            }
            $command->unlock();
        });

        $definition = $this->getDefinition();
        $definition->addOption(new InputOption("no-time-limit", null, InputOption::VALUE_NONE, "Prevent the command from ending at the regular time limit"));
    }


    /**
     * Search the specified path for command classes and add them to the application.
     *
     * Files/classes must be named using CamelCase and must end in Command.
     * Each uppercase character will be converted to lowercase and preceded by a hyphen.
     * Directories will represent namespaces and each separater will be replaced with a colon.
     * eg Category/Topic/RunCommand.php will create a command called category:topic:run
     *
     * @param string $path The path to search for the command classes
     *
     * @return static
     */
    public function loadCommands($path, $namespace = "")
    {
        $commands = [];

        # Get the realpath so we can strip it from the start of the filename
        $realpath = realpath($path);

        $finder = (new Finder)->files()->in($path)->name("/[A-Z].*Command.php/");
        foreach ($finder as $file) {

            # Get the realpath of the file and ensure the class is loaded
            $filename = $file->getRealPath();
            require_once $filename;

            # Convert the filename to a class
            $class = $filename;
            $class = str_replace($realpath, "", $class);
            $class = str_replace(".php", "", $class);
            $class = str_replace("/", "\\", $class);

            # Convert the class name to a command name
            $command = $class;
            if (substr($command, 0, 1) == "\\") {
                $command = substr($command, 1);
            }
            $command = preg_replace_callback("/^([A-Z])(.*)Command$/", function($match) {
                return strtolower($match[1]) . $match[2];
            }, $command);
            $command = preg_replace_callback("/(\\\\)?([A-Z])/", function($match) {
                $result = ($match[1]) ? ":" : "-";
                $result .= strtolower($match[2]);
                return $result;
            }, $command);

            # Create an instance of the command class
            $class = $namespace . $class;
            $commands[] = new $class($command);
        }

        if (count($commands) < 1) {
            throw new \InvalidArgumentException("No commands were found in the path (" . $path . ")");
        }

        $this->addCommands($commands);

        return $this;
    }


    /**
     * {@inheritDoc}
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if ($input === null) {
            $input = new ArgvInput();
        }
        if ($output === null) {
            $output = new Output();
        }
        parent::run($input, $output);
    }


    /**
     * Override configureIO() so that we can check if the global --no-time-limit option was set.
     *
     * {@inheritDoc}
     */
    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasParameterOption(["--no-time-limit", "-x"])) {
            $this->timeLimit = false;
        } else {
            $this->timeLimit = true;
        }
        parent::configureIO($input, $output);
    }


    /**
     * Allow commands to check if the application currently allows time limiting or prevents it.
     *
     * @return boolean
     */
    public function timeLimit()
    {
        return $this->timeLimit;
    }


    /**
     * Get the path to the drectory where lock files are stored.
     *
     * Also checks if the directory exists and attempts to create it if not
     *
     * @return string
     */
    public function getLockPath()
    {
        if (!is_dir($this->lockPath)) {
            (new Filesystem)->mkdir($this->lockPath);
        }
        return $this->lockPath;
    }


    /**
     * Set the path for the drectory where lock files are stored.
     *
     * @return static
     */
    public function setLockPath($path)
    {
        if (!is_dir($path)) {
            (new Filesystem)->mkdir($path);
        }
        if (!$realpath = realpath($path)) {
            throw new \InvalidArgumentException("The directory (" . $path . ") is unavailable");
        }

        $this->lockPath = $realpath;

        return $this;
    }


    /**
     * {@inheritDoc}
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new CompletionCommand;
        return $commands;
    }


    /**
     * Run another command, usually from within the currently running command.
     *
     * @param string $command The fully namespaced name of the command to run
     * @param array $options An array of options to pass to the command
     * @param InputInterface $current The input used in the parent command
     * @param OutputInterface $output The output used in the parent command
     *
     * @return int 0 if everything went fine, or an error code
     */
    public function runCommand($command, array $options, InputInterface $current, OutputInterface $output)
    {
        # The first input argument must be the command name
        array_unshift($options, $command);
        $input = new ArrayInput($options);

        if (!$current->isInteractive()) {
            $input->setInteractive(false);
        }

        $command = $this->get($command);
        return $this->doRunCommand($command, $input, $output);
    }
}
