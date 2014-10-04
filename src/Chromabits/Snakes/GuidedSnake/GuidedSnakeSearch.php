<?php

namespace Chromabits\Snakes\GuidedSnake;

use Chromabits\Snakes\PathNode;

/**
 * Class GuidedSnakeSearch
 *
 * @package Chromabits\Snakes\GuidedSnake
 */
class GuidedSnakeSearch
{
    protected $nodeGraph;

    protected $dimensions;

    /**
     * Initialize the search
     *
     * @param int $dimensions
     */
    public function __construct($dimensions = 3)
    {
        // Set dimensions
        $this->dimensions = $dimensions;

        // Initialize the graph
        $this->nodeGraph = new GuidedSnakeGraph($this->dimensions);
    }

    public function run($iterations = 1)
    {
        $initialNode = new PathNode($this->dimensions);

        $this->step($initialNode);

        print_r($this->nodeGraph);
    }

    public function step(PathNode $previousNode, $exploredPaths = array())
    {
        // TODO: If previousNode is null, try to find the next node using the graph
        // TODO: This should be fetched from the graph eventually
        //$unexploredNeighbors = $previousNode->computeNeighbors();

        // Make a random choice
        /** @var PathNode $randomChoice */
        $randomChoice = $previousNode->getRandomNextNode($previousNode->getUsedNodes());

        // Check if we have reached a dead-end
        if (is_null($randomChoice)) {
            $this->nodeGraph->addPathLengthStatistic($previousNode);
            return;
        }

        // Otherwise, prepare for the next step
        $randomChoiceNode = new PathNode($this->dimensions, $randomChoice);
        $randomChoiceNode->setParentNode($previousNode);

        $this->step($randomChoiceNode);
    }
} 