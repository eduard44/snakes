<?php

namespace Chromabits\Snakes\GuidedSnake;

use Chromabits\Snakes\PathNode;
use Exception;

/**
 * Class GuidedSnakeGraph
 *
 * Graph-like structure that keeps track of the best snakes on every node
 * and the best snake in general
 *
 * @package Chromabits\Snakes\GuidedSnake
 */
class GuidedSnakeGraph
{
    protected $bestPath = null;
    protected $bestPathLength = 0;

    protected $graphNodes = [];

    protected $dimensions = 3;

    /**
     * Initialize the graph
     *
     * @param int $dimensions
     */
    public function __construct($dimensions = 3)
    {
        $this->dimensions = $dimensions;
    }

    // TODO: Function to get which neighbors the search should explore next
    // TODO: Function to get a node to start searching at (must support some sort of exclude of explored nodes)

    /**
     * Add a statistic about a path (not necessarily the best)
     *
     * @param PathNode $pathNode
     * @throws Exception
     */
    public function addPathLengthStatistic(PathNode $pathNode)
    {
        $graphNode = $this->findOrCreate($pathNode->getStringIdentifier());

        $graphNode->setPath($pathNode);

        $bestPathTail = $graphNode->getBestPathTail();

        // Update statistic about the best path (if any)
        if (!is_null($bestPathTail)) {
            $this->updateBestPath($bestPathTail);
        }
    }

    /**
     * Attempt to update the best path
     *
     * @param PathNode $node
     */
    protected function updateBestPath(PathNode $node)
    {
        $pathLength = $node->getLength();

        if ($this->bestPathLength < $pathLength) {
            $this->bestPathLength = $pathLength;

            $this->bestPath = $node;
        }
    }

    /**
     * Find the specified node in the array
     *
     * If it is not loaded, it will be initialized automatically (lazy-loading)
     *
     * @param $nodeStringIdentifier
     * @return GuidedSnakeNode
     */
    protected function findOrCreate($nodeStringIdentifier)
    {
        // If the node is not in the array, initialize it
        if (!array_key_exists($nodeStringIdentifier, $this->graphNodes)) {
            $this->graphNodes[$nodeStringIdentifier] = new GuidedSnakeNode($this->dimensions, $nodeStringIdentifier);
        }

        return $this->graphNodes[$nodeStringIdentifier];
    }

    /**
     * Update two-way references of a specific node and its neighbors
     *
     * @param $nodeStringIdentifier
     * @throws Exception
     */
    protected function updateNodeNeighborReferences($nodeStringIdentifier)
    {
        // First check that the node is loaded in the array (this should be handled by other parts of the code)
        if (!array_key_exists($nodeStringIdentifier, $this->graphNodes)) {
            throw new Exception('This node is not currently loaded in memory');
        }

        // Go over every neighbor of the node, and check if they are the in the node array

        /** @var GuidedSnakeNode $graphNode */
        $graphNode = $this->graphNodes[$nodeStringIdentifier];
        foreach ($graphNode->computeNeighbors() as $neighborStringIdentifier) {
            if (array_key_exists($neighborStringIdentifier, $this->graphNodes)) {
                /** @var GuidedSnakeNode $neighborNode */
                $neighborNode = $this->graphNodes[$neighborStringIdentifier];

                // Add two-way reference
                $graphNode->addNeighborReference($neighborNode);
                $neighborNode->addNeighborReference($graphNode);
            }
        }
    }
} 