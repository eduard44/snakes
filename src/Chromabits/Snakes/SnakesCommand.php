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
            )
            ->addOption(
                'randomized',
                'r',
                InputOption::VALUE_NONE,
                'Randomize search'
            )
            ->addOption(
                'iterations',
                'i',
                InputOption::VALUE_REQUIRED,
                'Number of iterations'
            )
            ->addOption(
                'learn',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Number of learning iterations',
                1
            )
            ->addOption(
                'penalize',
                null,
                InputOption::VALUE_NONE,
                'Penalize the learning model if it does not provide better results'
            )
            ->addOption(
                'remember',
                'm',
                InputOption::VALUE_NONE,
                'Use same model for all learning steps'
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
        // Allow PHP to use a lot of memory
        ini_set('memory_limit', '-1');

        // Create a new search
        $search = new Search($input->getOption('dimension'));

        // Set the search as randomized
        if ($input->getOption('randomized')) {
            $search->setRandomized(true);
        }

        // Set number of iterations
        if ($input->getOption('iterations')) {
            $search->setIterations($input->getOption('iterations'));
        }

        // Set number of learning iterations
        if ($input->getOption('learn')) {
            $search->setLearningIterations($input->getOption('learn'));
        }

        if ($input->getOption('penalize')) {
            $search->setPenalize(true);
        }

        if ($input->getOption('remember')) {
            $search->setForget(false);
        };

        // Execute the search
        $result = $search->run($output);

        // Print out output (if specified)
        if ($input->getOption('print')) {
            $output->write(print_r($result, true));
        }

        // Print out the length of the path found
        $output->writeln("\n" . 'Largest path found was: ' . count($result));
    }
} 