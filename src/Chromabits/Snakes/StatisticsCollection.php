<?php

namespace Chromabits\Snakes;

/**
 * Class StatisticsCollection
 *
 * @package Chromabits\Snakes
 */
class StatisticsCollection {
    protected $iterationResults;
    protected $largestPathLength;
    protected $dimensions;

    /**
     * Constructor
     *
     * @param $dimensions
     */
    function __construct($dimensions)
    {
        $this->iterationResults = array();

        $this->largestPathLength = 0;

        $this->dimensions = $dimensions;
    }

    public function addIterationResult(array $pathArray)
    {
        $this->iterationResults[] = $pathArray;

        $pathLength = count($pathArray);

        if ($pathLength > $this->largestPathLength) {
            $this->largestPathLength = $pathLength;
        }
    }

    public function computeWeightedNodes($penalty)
    {
        if ($penalty > 0) {
            $penalty = 0;
        }

        if ($this->largestPathLength == 0) {
            return new WeightedNodeCollection(array());
        }

        $nodes = array();
        $largestPathLength = $this->largestPathLength;

        // Go through each iteration path
        foreach ($this->iterationResults as $pathArray) {
            $pathLength = count($pathArray);
            $pathValue = ($pathLength/$largestPathLength) + $penalty;

            // Go through each edge and set weight info
            foreach ($pathArray as $edgeArray) {
                $nodeIdentifier = $edgeArray[0];
                $neighborIdentifier = $edgeArray[1];

                if (!array_key_exists($nodeIdentifier, $nodes)) {
                    $nodes[$nodeIdentifier] = new WeightedNode($this->dimensions, $nodeIdentifier);
                }

                /** @var WeightedNode $weightedNode */
                $weightedNode = $nodes[$nodeIdentifier];

                $weightedNode->addWeightStatistic($neighborIdentifier, $pathValue);
            }
        }

        return new WeightedNodeCollection($nodes);
    }
} 