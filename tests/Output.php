<?php

namespace duncan3dc\ConsoleTests;

use Symfony\Component\Console\Output\OutputInterface;

use function is_array;

class Output extends \duncan3dc\SymfonyCLImate\Output
{
    /** @var string */
    private $content;


    /**
     * @param string|string[] $messages
     * @param bool $newline
     * @param int $type
     */
    public function write($messages, $newline = false, $type = OutputInterface::OUTPUT_NORMAL): void
    {
        if ($type >= $this->getVerbosity()) {
            return;
        }

        if (!is_array($messages)) {
            $messages = [$messages];
        }

        foreach ($messages as $message) {
            $this->content .= $message;
            if ($newline) {
                $this->content .= "\n";
            }
        }
    }


    /**
     * @param string|string[] $messages
     * @param int $type
     */
    public function writeln($messages, $type = OutputInterface::OUTPUT_NORMAL): void
    {
        $this->write($messages, true);
    }


    public function getContent(): string
    {
        return $this->content;
    }
}
