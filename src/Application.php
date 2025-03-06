<?php

namespace duncan3dc\Console;

use duncan3dc\SymfonyCLImate\Output;
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
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\FlockStore;

/**
 * {@inheritDoc}
 */
class Application extends \Symfony\Component\Console\Application
{
    /**
     * The return code used when a command cannot run as it is locked
     */
    public const STATUS_LOCKED = 201;

    /**
     * The return code used when a permissions issue prevented a command from running
     */
    public const STATUS_PERMISSIONS = 202;

    private ?LockFactory $lockFactory = null;

    /**
     * @var string $lockPath The directory to store command locks in
     */
    protected string $lockPath = "/tmp/console-locks";

    /**
     * @var bool $timeLimit Whether the application currently allows time limits for commands
     */
    protected bool $timeLimit = true;

    /**
     * @var bool $showResourceInfo Whether the application should show resource info for commands
     */
    private bool $showResourceInfo = true;


    public function __construct(string $name = "UNKNOWN", string $version = "UNKNOWN")
    {
        parent::__construct($name, $version);

        $dispatcher = new EventDispatcher();
        $this->setDispatcher($dispatcher);

        # Attempt to acquire a unique lock on the command
        $dispatcher->addListener(ConsoleEvents::COMMAND, function (ConsoleCommandEvent $event) {
            $command = $event->getCommand();
            if ($command instanceof Command) {
                $command->lock($event->getOutput());
            }
        });

        # If we achieved a lock above then unlock it after the job finishes
        $dispatcher->addListener(ConsoleEvents::TERMINATE, function (ConsoleTerminateEvent $event) {
            $command = $event->getCommand();
            if ($command instanceof Command) {
                $command->unlock();
            }
        });

        $definition = $this->getDefinition();
        $definition->addOption(new InputOption("no-time-limit", null, InputOption::VALUE_NONE, "Prevent the command from ending at the regular time limit"));
        $definition->addOption(new InputOption("show-resource-info", null, InputOption::VALUE_NONE, "Show the resource info it took to run a command"));
        $definition->addOption(new InputOption("hide-resource-info", null, InputOption::VALUE_NONE, "Hide the resource info it took to run a command"));
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
     * @param string $namespace The parent namespace that these commands are in
     * @param string $suffix The class suffix to search for
     *
     * @return $this
     */
    public function loadCommands(string $path, string $namespace = "", string $suffix = "Command"): Application
    {
        $commands = [];

        # Get the realpath so we can strip it from the start of the filename
        $realpath = (string) realpath($path);

        $finder = (new Finder())->files()->in($path)->name("/[A-Z].*{$suffix}.php/");
        foreach ($finder as $file) {
            # Get the realpath of the file and ensure the class is loaded
            $filename = (string) $file->getRealPath();
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
            $command = (string) preg_replace_callback("/^([A-Z])(.*){$suffix}$/", function ($match) {
                return strtolower($match[1]) . $match[2];
            }, $command);
            $command = preg_replace_callback("/(\\\\)?([A-Z])/", function ($match) {
                $result = ($match[1]) ? ":" : "-";
                $result .= strtolower($match[2]);
                return $result;
            }, $command);

            $class = $namespace . $class;

            # Don't attempt create things we can't instantiate
            if (!class_exists($class)) {
                 continue;
            }
            $reflected = new \ReflectionClass($class);
            if (!$reflected->isInstantiable()) {
                continue;
            }

            /** @var \Symfony\Component\Console\Command\Command $object */
            $object = new $class($command);
            $commands[] = $object;
        }

        if (count($commands) < 1) {
            throw new \InvalidArgumentException("No commands were found in the path (" . $path . ")");
        }

        $this->addCommands($commands);

        return $this;
    }


    public function run(?InputInterface $input = null, ?OutputInterface $output = null): int
    {
        if ($input === null) {
            # Allow namespace contents to be listed when they are entered with a trailing colon
            if (isset($_SERVER["argv"][1]) && substr($_SERVER["argv"][1], -1) === ":") {
                # Re-create the argv contents to simulate the user running the list command
                $argv = [$_SERVER["argv"][0]];
                $argv[] = "list";
                $argv[] = substr($_SERVER["argv"][1], 0, -1);
                $argv += array_slice($_SERVER["argv"], 2, count($_SERVER["argv"]) - 2);
                $_SERVER["argv"] = $argv;
            }
            $input = new ArgvInput();
        }

        if ($output === null) {
            $output = new Output();
        }

        return parent::run($input, $output);
    }


    protected function configureIO(InputInterface $input, OutputInterface $output): void
    {
        parent::configureIO($input, $output);

        if ($input->hasParameterOption("--no-time-limit")) {
            $this->timeLimit = false;
        } else {
            $this->timeLimit = true;
        }

        # In quiet mode, don't display resource info by default
        if ($output->isQuiet()) {
            $this->showResourceInfo = false;
        }

        if ($input->hasParameterOption("--show-resource-info")) {
            $this->showResourceInfo = true;
        } elseif ($input->hasParameterOption("--hide-resource-info")) {
            $this->showResourceInfo = false;
        }

        if ($output->isVeryVerbose()) {
            $handler = new \NunoMaduro\Collision\Handler();
            $handler->setOutput($output);
            $provider = new \NunoMaduro\Collision\Provider(null, $handler);
            $provider->register();
            $this->setCatchExceptions(false);
        }
    }


    /**
     * Allow commands to check if the application currently allows time limiting or prevents it.
     */
    public function timeLimit(): bool
    {
        return $this->timeLimit;
    }


    /**
     * Allow commands to check if the application should show resource info or not.
     */
    public function showResourceInfo(): bool
    {
        return $this->showResourceInfo;
    }


    /**
     * Set the lock store to use.
     */
    public function setLockStore(PersistingStoreInterface $store): LockFactory
    {
        $this->lockFactory = new LockFactory($store);
        return $this->lockFactory;
    }


    /**
     * Get the lock factory in use.
     */
    public function getLockFactory(): LockFactory
    {
        if ($this->lockFactory !== null) {
            return $this->lockFactory;
        }

        if (!is_dir($this->lockPath)) {
            (new Filesystem())->mkdir($this->lockPath);
        }
        $store = new FlockStore($this->lockPath);

        return $this->setLockStore($store);
    }


    /**
     * Set the path for the directory where lock files are stored.
     *
     * @param string $path The path to use
     *
     * @return $this
     */
    public function setLockPath(string $path): Application
    {
        if (!is_dir($path)) {
            (new Filesystem())->mkdir($path);
        }
        if (!$realpath = realpath($path)) {
            throw new \InvalidArgumentException("The directory (" . $path . ") is unavailable");
        }

        $this->lockPath = $realpath;

        return $this;
    }


    protected function getDefaultCommands(): array
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new CompletionCommand();
        return $commands;
    }


    /**
     * Run another command, usually from within the currently running command.
     *
     * @param string $command The fully namespaced name of the command to run
     * @param array<string, string> $options An array of options to pass to the command
     * @param InputInterface $current The input used in the parent command
     * @param OutputInterface $output The output used in the parent command
     *
     * @return int 0 if everything went fine, or an error code
     */
    public function runCommand(string $command, array $options, InputInterface $current, OutputInterface $output): int
    {
        $input = new ArrayInput($options);

        if (!$current->isInteractive()) {
            $input->setInteractive(false);
        }

        $command = $this->get($command);
        return $this->doRunCommand($command, $input, $output);
    }
}
