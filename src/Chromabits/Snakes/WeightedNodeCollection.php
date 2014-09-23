<?php

namespace Chromabits\Snakes;

/**
 * Class WeightedNodeCollection
 *
 * @package Chromabits\Snakes
 */
class WeightedNodeCollection
{
    /**
     * @var array
     */
    protected $nodes;

    /**
     * @param array $nodes
     */
    public function __construct(array $nodes)
    {
        $this->nodes = $nodes;
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function hasStatisticsFor($identifier)
    {
        return array_key_exists($identifier, $this->nodes);
    }

    /**
     * @param string $identifier
     * @return WeightedNode
     */
    public function getNode($identifier)
    {
        return $this->nodes[$identifier];
    }
} 