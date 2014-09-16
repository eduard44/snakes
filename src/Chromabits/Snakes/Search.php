<?php

namespace Chromabits\Snakes;

use Exception;

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

    public function setupInitialNode()
    {
        $this->initialNode = new Node($this->dimensions);
    }

    public function step(Node $currentNode, array $unavailable, array $path)
    {
        $currentString = $currentNode->getStringIdentifier();
        $neighbors = $currentNode->computeNeighbors();

        $exploredPaths = array();

        foreach ($neighbors as $neighborString) {
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

    public function run()
    {
        return $this->step($this->initialNode, array(), array());
    }
} 