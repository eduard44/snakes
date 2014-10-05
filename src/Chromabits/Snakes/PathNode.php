<?php

namespace Chromabits\Snakes;

/**
 * Class PathNode
 *
 * Store node paths in a single linked-list
 *
 * @package Chromabits\Snakes
 */
class PathNode extends Node
{
    /**
     * @var PathNode
     */
    protected $parentNode = null;

    protected $cachedUsedNodes = null;

    public function setParentNode(PathNode $parentNode)
    {
        $this->parentNode = $parentNode;

        // Update the used node cache array
        $stringIdentifier = $this->getStringIdentifier();
        $this->cachedUsedNodes = array_merge($this->parentNode->getUsedNodes($this), [$stringIdentifier]);
    }

    public function getParentNode()
    {
        return $this->parentNode;
    }

    /**
     * Get the path length up to this point
     *
     * @return int
     */
    public function getLength()
    {
        if (is_null($this->parentNode)) {
            return 1;
        }

        $length = 1;

        /** @var PathNode $currentNode */
        $currentNode = $this->parentNode;
        $exploring = true;

        while ($exploring) {
            $currentParent = $currentNode->getParentNode();

            $length += 1;

            if (is_null($currentParent)) {
                $exploring = false;
            } else {
                $currentNode = $currentParent;
            }
        }

        return $length;
    }

    /**
     * Get an array of all the nodes used to this point
     *
     * This is used to apply the snake rules (ech node only has two neighbors in the path)
     *
     * @return array|null
     */
    public function getUsedNodes(PathNode $to = null)
    {
        $stringIdentifier = $this->getStringIdentifier();

        $thisArray = [$stringIdentifier];

        // If we have no parent, this is the only used node
        if (is_null($this->parentNode)) {
            if (!is_null($to)) {
                return array_unique(array_merge($thisArray, array_diff($this->computeNeighbors(), [$to->getStringIdentifier()])));
            }

            return $thisArray;
        }

        if (is_null($to)) {
            return array_unique(array_merge($this->parentNode->getUsedNodes($this), [$stringIdentifier]));
        }

        return array_unique(array_merge($this->parentNode->getUsedNodes($this), array_diff($this->computeNeighbors(), [$to->getStringIdentifier()]), [$stringIdentifier]));
    }

    /**
     * Pick a random neighbor (expect the ones specified)
     *
     * @param string[] $except
     * @return string|null
     */
    public function getRandomNextNode(array $except = [])
    {
        $remainingOptions = array_values(array_diff($this->computeNeighbors(), $except));

        $remainingOptions = array_values(array_diff($remainingOptions, $this->getUsedNodes()));

        if (empty($remainingOptions)) {
            return null;
        }

        $maxIndex = count($remainingOptions) - 1;

        $randomChoiceIndex = mt_rand(0, $maxIndex);

        return $remainingOptions[$randomChoiceIndex];
    }

    public function toString()
    {
        if (is_null($this->parentNode)) {
            return $this->getStringIdentifier();
        }

        return $this->parentNode->toString() . ' --> ' . $this->getStringIdentifier();
    }
} 