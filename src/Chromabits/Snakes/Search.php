<?php

namespace Chromabits\Snakes;

use Exception;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Search
 *
 * @package Chromabits\Snakes
 */
class Search
{
    /**
     * @var int
     */
    protected $dimensions;

    /**
     * @var Node
     */
    protected $initialNode;

    /**
     * @var array
     */
    protected $unavailableNodesIdentifiers;

    /**
     * Whether the search is randomized or not
     *
     * @var bool
     */
    protected $randomized = false;

    /**
     * Number of iterations to run the search
     *
     * @var int
     */
    protected $interations = 1;

    /**
     * Constructor
     *
     * @param int $dimensions
     * @throws Exception
     */
    public function __construct($dimensions = 3)
    {
        if ($dimensions < 3) {
            throw new Exception('Min dimension is 3');
        }

        $this->dimensions = $dimensions;

        $this->setupInitialNode();
    }

    /**
     * Create the initial node
     */
    public function setupInitialNode()
    {
        $this->initialNode = new Node($this->dimensions);
    }

    /**
     * Recursive search step
     *
     * @param Node $currentNode
     * @param array $unavailable
     * @param array $path
     * @return array
     */
    public function step(Node $currentNode, array $unavailable, array $path)
    {
        $currentString = $currentNode->getStringIdentifier();
        $neighbors = $currentNode->computeNeighbors();

        $exploredPaths = array();

        $randomChoice = rand(0, $this->dimensions);
        $randomCount = 0;

        foreach ($neighbors as $neighborString) {
            // Randomization
            if ($this->isRandomized()) {
                if ($randomChoice != $randomCount) {
                    $randomCount++;
                    continue;
                }

                $randomCount++;
            }

            // Check if neighbor is available
            if (in_array($neighborString, $unavailable)) {
                continue;
            }

            // Recursively navigate into the node
            $neighbor = new Node($this->dimensions, $neighborString);

            // Clone paths
            $neighborPath = array_merge($path);

            // Add path taken
            $neighborPath[] = array($currentString, $neighborString);

            // Add this node and other neighbors
            $neighborUnavailable[] = $currentString;

            $otherNeighbors = array_diff($neighbors, array($neighborString));

            $neighborUnavailable = array_merge($unavailable, array($currentString), $otherNeighbors);

            $newPath = $this->step($neighbor, $neighborUnavailable, $neighborPath);

            $exploredPaths[] = $newPath;
        }

        // Find largest path explored
        $largestPathLength = 0;
        $largestPath = $path;

        foreach ($exploredPaths as $exploredPath) {
            $length = count($exploredPath);

            if ($length > $largestPathLength) {
                $largestPath = $exploredPath;
                $largestPathLength = $length;
            }
        }

        return $largestPath;
    }

    /**
     * Execute the search
     *
     * @return array
     */
    public function run(OutputInterface $output)
    {
        // Keep track of best results
        $bestResult = array();
        $bestResultCount = 0;

        // Display a progress bar
        $progress = new ProgressBar($output, $this->getInterations());

        // Make the bar redraw less if it is a large number of iterations
        if ($this->getInterations() > 1000) {
            $progress->setRedrawFrequency(100);
        }

        // Start drawing the progress bar
        $progress->start();

        // Run for requested number of iterations
        for ($i = 0; $i < $this->getInterations(); $i++) {
            $result = $this->step($this->initialNode, array(), array());

            $resultCount = count($result);

            // Check if the current iteration was better than the last best
            if ($resultCount > $bestResultCount) {
                $bestResult = $result;
                $bestResultCount = $resultCount;
            }

            $progress->advance();
        }

        $progress->finish();

        return $bestResult;
    }

    /**
     * @return boolean
     */
    public function isRandomized()
    {
        return $this->randomized;
    }

    /**
     * @param boolean $randomized
     */
    public function setRandomized($randomized)
    {
        $this->randomized = $randomized;
    }

    /**
     * @return int
     */
    public function getInterations()
    {
        return $this->interations;
    }

    /**
     * @param int $interations
     */
    public function setInterations($interations)
    {
        $this->interations = $interations;
    }
} 