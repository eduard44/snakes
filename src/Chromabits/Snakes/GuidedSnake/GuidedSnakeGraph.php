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

    protected $weightedNextNode = false;

    /**
     * Initialize the graph
     *
     * @param int $dimensions
     */
    public function __construct($dimensions = 3)
    {
        $this->dimensions = $dimensions;
    }

    /**
     * @return null|PathNode
     * @throws Exception
     */
    public function getNextExplorableNode()
    {
        if (count($this->graphNodes) < 1) {
            throw new Exception('No nodes defined');
        }

        $explorableNodes = array_filter($this->graphNodes, function (GuidedSnakeNode $graphNode) {
            return !$graphNode->isFullyExploredOnAll();
        });

        usort(
            $explorableNodes,
            function (GuidedSnakeNode $graphNodeA, GuidedSnakeNode $graphNodeB) {
                $nodeA = $graphNodeA->getBestPathLength();
                $nodeB = $graphNodeB->getBestPathLength();

                if ($nodeA > $nodeB) {
                    return -1;
                } elseif ($nodeA < $nodeB) {
                    return 1;
                }

                return 0;
            }
        );

        // OPTIONAL: Use a weighted approach
        if ($this->weightedNextNode) {
            $weightedIndexes = [];

            /** @var GuidedSnakeNode $explorableNode */
            foreach ($explorableNodes as $key => $explorableNode) {
                $weightedIndexes[$key] =
                    (($explorableNode->getBestPathLength() / $this->bestPathLength) * 0.8) + 0.2;
            }

            $weightedIndex = $this->getRandomWeightedElement($weightedIndexes);

            return $explorableNodes[$weightedIndex]->getBestPathTail();
        }

        $randomIndex = mt_rand(0, count($explorableNodes) - 1);

        return $explorableNodes[$randomIndex]->getBestPathTail();
    }

    // TODO: Function to get which neighbors the search should explore next
    // TODO: Function to get a node to start searching at (must support some sort of exclude of explored nodes)

    /**
     * Add a statistic about a path (not necessarily the best)
     *
     * @param PathNode $pathNode
     * @param bool $initial
     * @throws Exception
     */
    public function addPathLengthStatistic(PathNode $pathNode, $initial = false)
    {
        $graphNode = $this->findOrCreate($pathNode->getStringIdentifier(), $initial);

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
    protected function findOrCreate($nodeStringIdentifier, $initial)
    {
        // If the node is not in the array, initialize it
        if (!array_key_exists($nodeStringIdentifier, $this->graphNodes)) {
            $this->graphNodes[$nodeStringIdentifier] = new GuidedSnakeNode($this->dimensions, $nodeStringIdentifier);

            if ($initial) {
                $this->graphNodes[$nodeStringIdentifier]->setInitial(true);
            }
        }

        return $this->graphNodes[$nodeStringIdentifier];
    }

    public function getBestPathLength()
    {
        return $this->bestPathLength;
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

    /**
     * @param array $weightedValues
     * @throws Exception
     * @return int|string
     */
    protected function getRandomWeightedElement(array $weightedValues) {
        $rand = mt_rand(1, (int) array_sum($weightedValues));

        foreach ($weightedValues as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $key;
            }
        }

        throw new Exception('This should not happen');
    }
} 