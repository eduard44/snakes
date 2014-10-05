<?php

namespace Chromabits\Snakes\GuidedSnake;

use Chromabits\Snakes\Node;
use Chromabits\Snakes\PathNode;
use Exception;
use InvalidArgumentException;

/**
 * Class GuidedSnakeNode
 *
 * Advanced structure for keeping statistics about snake paths
 *
 * @package Chromabits\Snakes\GuidedSnake
 */
class GuidedSnakeNode extends Node
{
    protected $neighborReferences = [];

    // Each node identifier and whether or not the node has been fully explored when coming from one of them
    protected $cachedIsFullyExplored = [];

    // Keep track of best paths
    protected $bestPaths = [];

    protected $bestPathLengths = [];

    protected $bestPathLength = 0;

    protected $deadEnd = false;

    protected $initial = false;

    /**
     * @param boolean $initial
     */
    public function setInitial($initial)
    {
        $this->initial = $initial;
    }

    /**
     * Get the best unexplored path or null if none found
     *
     * @return PathNode|null
     */
    public function getBestUnexploredPathTail()
    {
        if (count($this->bestPaths) < 1) {
            return null;
        }

        $bestUnexploredPath = null;
        $bestLength = 0;
        foreach ($this->bestPathLengths as $pathIndex => $pathLength) {
            /** @var PathNode $currentPath */
            $currentPath = $this->bestPaths[$pathIndex];

            if ($pathLength > $bestLength && !$this->isFullyExplored($currentPath->getParentNode())) {
                $bestUnexploredPath = $currentPath;
            }
        }

        if (is_null($bestUnexploredPath)) {
            return null;
        }

        return $bestUnexploredPath;
    }

    /**
     * Add a reference to a neighbor
     *
     * @param GuidedSnakeNode $node
     * @throws Exception
     */
    public function addNeighborReference(GuidedSnakeNode $node)
    {
        if (!$this->isNear($node)) {
            throw new Exception('This node could ne be added since it\'s not really near');
        }

        // Keep a reference to this child
        $this->neighborReferences[$node->getStringIdentifier()] = $node;
    }

    /**
     * Update the best path reference
     *
     * @param PathNode $pathTail
     * @throws Exception
     */
    public function setPath(PathNode $pathTail)
    {
        // Check that the node identifier match
        if ($pathTail->getStringIdentifier() != $this->getStringIdentifier()) {
            throw new Exception('Node identifiers do not match');
        }

        // Check that we have the node we are coming from
        $comingFrom = $pathTail->getParentNode();

        if (is_null($comingFrom)) {
            return;
        }

        $comingFromStringIdentifier = $comingFrom->getStringIdentifier();

        // Check if the path is longer than anything we have seen for that node before
        $pathLength = $pathTail->getLength();
        if (array_key_exists($comingFromStringIdentifier, $this->bestPathLengths)) {
            if ($this->bestPathLengths[$comingFromStringIdentifier] < $pathLength) {
                // Set the new best
                $this->bestPathLengths[$comingFromStringIdentifier] = $pathLength;

                $this->bestPaths[$comingFromStringIdentifier] = $pathTail;
            }
        } else {
            // Set the new best
            $this->bestPathLengths[$comingFromStringIdentifier] = $pathLength;

            $this->bestPaths[$comingFromStringIdentifier] = $pathTail;
        }

        // Keep a statistic of the best path overall
        if ($pathLength > $this->bestPathLength) {
            $this->bestPathLength = $pathLength;
        }
    }

    /**
     * Compute if this node is fully explored
     *
     * @param string $comingFromStringIdentifier
     * @return bool
     */
    public function isFullyExplored($comingFromStringIdentifier)
    {
        // Type check
        if (is_null($comingFromStringIdentifier) || !is_string($comingFromStringIdentifier)) {
            throw new InvalidArgumentException();
        }

        // If we have already calculated that it is fully explored, then avoid calculating all that again
        if (array_key_exists($comingFromStringIdentifier, $this->cachedIsFullyExplored)
            && $this->cachedIsFullyExplored[$comingFromStringIdentifier]) {
            return true;
        }

        // If we don't have all neighbors, we haven't really explored this node
        $otherNodesCount = ($this->dimensions - 1);
        if (count($this->neighborReferences) != $otherNodesCount) {
            return false;
        }

        // Compute if this node is explored
        $exploredNeighbors = 0;
        $nodeIdentifier = $this->getStringIdentifier();

        /** @var GuidedSnakeNode $neighbor */
        foreach($this->neighborReferences as $neighborIdentifier => $neighbor) {
            // Skip if this is the node we are coming from
            if ($neighborIdentifier == $comingFromStringIdentifier) {
                continue;
            }

            // Otherwise, check if the node is explored coming from this one
            if ($neighbor->isFullyExplored($nodeIdentifier)) {
                $exploredNeighbors += 1;
            }
        }

        // If the total of explore nodes equals the total of remaining
        // neighbors, then it means it is explored
        $explored = false;

        if ($exploredNeighbors == $otherNodesCount) {
            $explored = true;

            // Cache the result
            $this->cachedIsFullyExplored[$comingFromStringIdentifier] = true;
        }

        return $explored;
    }

    /**
     * Get whether or not all paths are explored
     *
     * @return bool
     */
    public function isFullyExploredOnAll()
    {
        $neighbors = $this->computeNeighbors();

        foreach ($neighbors as $neighborIdentifier) {
            if (!$this->isFullyExplored($neighborIdentifier)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the best path node or just null
     *
     * @throws Exception
     * @return null|PathNode
     */
    public function getBestPathTail()
    {
        // If we don't have any data, just return any
        if (empty($this->bestPathLengths)) {
            if ($this->initial) {
                $newNode = new PathNode($this->dimensions, $this->getStringIdentifier());

                $randomNode = new PathNode($this->dimensions, $newNode->getRandomNextNode());

                $randomNode->setParentNode($newNode);

                return $randomNode;
            } else {
                throw new Exception('This should not happen');
            }
        }

        $bestSoFar = 0;
        $bestPathIndex = null;

        foreach ($this->bestPathLengths as $stringIdentifier => $pathLength) {
            if ($pathLength > $bestSoFar) {
                $bestSoFar = $pathLength;
                $bestPathIndex = $stringIdentifier;
            }
        }

        return $this->bestPaths[$bestPathIndex];
    }

    public function getBestPathLength()
    {
        return $this->bestPathLength;
    }
} 