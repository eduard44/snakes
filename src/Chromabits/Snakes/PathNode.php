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
        $this->cachedUsedNodes = array_merge($this->parentNode->getUsedNodes(), [$stringIdentifier]);
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
    public function getUsedNodes()
    {
        $stringIdentifier = $this->getStringIdentifier();

        // If we have no parent, this is the only used node
        if (is_null($this->parentNode)) {
            return [$stringIdentifier];
        }

        // If we have a parent but no cache, compute and cache
        if (!empty($this->cachedUsedNodes)) {
            $this->cachedUsedNodes = array_merge($this->parentNode->getUsedNodes(), [$stringIdentifier]);
        }

        // If it's cached, avoid computing and just return the cached array
        return $this->cachedUsedNodes;
    }

    /**
     * Pick a random neighbor (expect the ones specified)
     *
     * @param string[] $except
     * @return string|null
     */
    public function getRandomNextNode(array $except)
    {
        $remainingOptions = array_diff($this->computeNeighbors(), $except);

        if (empty($remainingOptions)) {
            return null;
        }

        $maxIndex = count($remainingOptions) - 1;

        $randomChoiceIndex = mt_rand(0, $maxIndex);

        return $remainingOptions[$randomChoiceIndex];
    }
} 