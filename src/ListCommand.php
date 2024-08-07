<?php

namespace duncan3dc\Console;

use duncan3dc\SymfonyCLImate\Output;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Terminal;

use function array_key_exists;
use function count;
use function is_string;
use function ksort;
use function sprintf;
use function strlen;
use function strpos;
use function strrpos;
use function substr;
use function trim;

class ListCommand extends Command
{
    private int $tabLength = 4;

    private int $padLength = 0;

    private int $maxWidth = 0;

    protected function configure(): void
    {
        $this
            ->setName("list")
            ->setDescription("Lists commands")
            ->addArgument("namespace", InputArgument::OPTIONAL, "Only show commands from this namespace");
    }

    protected function command(InputInterface $input, Output $output): int
    {
        $arnold = $this->getApplication();

        $this->maxWidth = (new Terminal())->getWidth();

        $namespace = $input->getArgument("namespace");
        if (!is_string($namespace)) {
            $namespace = null;
        }
        $commands = $arnold->all($namespace);
        ksort($commands);

        $namespaces = [];
        foreach ($commands as $name => $command) {
            $pos = strpos($name, ":");
            if ($pos === false) {
                continue;
            }
            $namespace = substr($name, 0, $pos);

            if (!array_key_exists($namespace, $namespaces)) {
                $namespaces[$namespace] = [
                    "description" => "",
                    "commands" => [],
                ];
            }
            $namespaces[$namespace]["commands"][$name] = $command->getDescription();

            $padLength = strlen($name) + 4;
            if ($padLength > $this->padLength) {
                $this->padLength = $padLength;
            }
        }

        # Hide any namespaces with too many commands
        if (!$input->getArgument("namespace") && !$output->isVerbose()) {
            $this->padLength = 0;
            foreach ($namespaces as $namespace => &$data) {
                if (count($data["commands"]) > 5) {
                    $data["commands"] = ["" => "Run `list {$namespace}` to see the available commands in this namespace"];
                } else {
                    foreach ($data["commands"] as $name => $null) {
                        $padLength = strlen((string) $name) + 4;
                        if ($padLength > $this->padLength) {
                            $this->padLength = $padLength;
                        }
                    }
                }
            }
            unset($data);
        }

        foreach ($namespaces as $namespace => $data) {
            $output->comment($namespace);
            foreach ($data["commands"] as $name => $description) {
                $this->outputInColumns(sprintf("%-{$this->tabLength}s", "") . $name, $description, $output);
            }
        }

        return 0;
    }

    private function outputInColumns(string $first, string $second, Output $output): void
    {
        $output->info()->inline($first);

        $padLength = $this->tabLength + $this->padLength;

        $padding = $padLength - strlen($first);
        if ($padding < 1) {
            $padding = 1;
        }
        $output->inline(sprintf("%-{$padding}s", ""));

        $maxLength = $this->maxWidth - $padLength - $this->tabLength;

        $content = trim($second);
        while (strlen($content) > $maxLength) {
            $string = substr($content, 0, $maxLength);
            $pos = strrpos($string, " ");
            if (!$pos) {
                break;
            }

            $string = substr($string, 0, $pos);
            $content = trim(substr($content, strlen($string)));

            $output->out($string);
            $output->inline(sprintf("%-{$padLength}s", ""));
        }

        $output->out($content);
    }
}
