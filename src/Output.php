<?php

namespace duncan3dc\Console;

use League\CLImate\CLImate;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Output class that allows all the functionality of CLImate, whilst also allowing standard ConsoleOutput behaviour.
 */
class Output extends CLImate implements OutputInterface
{
    /**
     * @var ConsoleOutput $console Internal cache of the ConsoleOutput object we are mimicking
     */
    protected $console;

    /**
     * Get the ConsoleOutput object we are mimicking.
     *
     * @return ConsoleOutput
     */
    protected function getConsoleOutput()
    {
        if (!$this->console) {
            $this->console = new ConsoleOutput;
        }
        return $this->console;
    }

    /**
     * {@inheritDoc}
     */
    public function write($messages, $newline = false, $type = OutputInterface::OUTPUT_NORMAL)
    {
        $this->getConsoleOutput()->write($messages, $newline, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function writeln($messages, $type = OutputInterface::OUTPUT_NORMAL)
    {
        $this->getConsoleOutput()->writeln($messages, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function setVerbosity($level)
    {
        $this->getConsoleOutput()->setVerbosity($level);
    }

    /**
     * {@inheritDoc}
     */
    public function getVerbosity()
    {
        return $this->getConsoleOutput()->getVerbosity();
    }

    /**
     * {@inheritDoc}
     */
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        $this->getConsoleOutput()->setFormatter($formatter);
    }

    /**
     * {@inheritDoc}
     */
    public function getFormatter()
    {
        return $this->getConsoleOutput()->getFormatter();
    }

    /**
     * {@inheritDoc}
     */
    public function isQuiet()
    {
        return $this->getConsoleOutput()->isQuiet();
    }

    /**
     * {@inheritDoc}
     */
    public function isVerbose()
    {
        return $this->getConsoleOutput()->isVerbose();
    }

    /**
     * {@inheritDoc}
     */
    public function isVeryVerbose()
    {
        return $this->getConsoleOutput()->isVeryVerbose();
    }

    /**
     * {@inheritDoc}
     */
    public function isDebug()
    {
        return $this->getConsoleOutput()->isDebug();
    }

    /**
     * {@inheritDoc}
     */
    public function setDecorated($decorated)
    {
        $this->getConsoleOutput()->setDecorated($decorated);
    }

    /**
     * {@inheritDoc}
     */
    public function isDecorated()
    {
        return $this->getConsoleOutput()->isDecorated();
    }
}
