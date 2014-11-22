<?php

namespace duncan3dc\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * {@inheritDoc}
 */
class Application extends \Symfony\Component\Console\Application
{
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
}
