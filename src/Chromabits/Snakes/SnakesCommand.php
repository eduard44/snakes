<?php

namespace Chromabits\Snakes;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SnakesCommand
 *
 * @package src\Chromabits\Snakes
 */
class SnakesCommand extends Command
{
    /**
     * Setup the command
     */
    public function configure()
    {
        $this->setName('snakes')
            ->addOption(
                'dimension',
                'd',
                InputOption::VALUE_REQUIRED,
                'Cube Dimension (min 3)',
                3
            )
            ->addOption(
                'print',
                'p',
                InputOption::VALUE_NONE,
                'Print result array with all path edges'
            );
    }

    /**
     * Run the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Create a new search
        $search = new Search($input->getOption('dimension'));

        // Execute the search
        $result = $search->run();

        // Print out output (if specified)
        if ($input->getOption('print')) {
            $output->write(print_r($result, true));
        }

        // Print out the length of the path found
        $output->writeln('Largest path found was: ' . count($result));
    }
} 