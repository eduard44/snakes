<?php

namespace Chromabits\Snakes;

/**
 * Class WeightedNode
 *
 * @package Chromabits\Snakes
 */
class WeightedNode extends Node
{
    const BASE_CHANCE = 0.06;

    protected $neighborWeights;

    /**
     * Constructor
     *
     * @param int $dimensions
     * @param string $identifier
     * @throws \Exception
     */
    public function __construct($dimensions = 3, $identifier = null)
    {
        parent::__construct($dimensions, $identifier);

        $this->neighborWeights = array();
    }

    /**
     * @param $neighbor
     * @param $weight
     */
    public function addWeightStatistic($neighbor, $weight)
    {
        if (empty($this->neighborWeights)) {
            $neighbors = $this->computeNeighbors();

            foreach ($neighbors as $neighbor) {
                // Give each node a small change
                $this->neighborWeights[$neighbor] = self::BASE_CHANCE;
            }
        }

        // Initialize if it doesn't exist
        if (!array_key_exists($neighbor, $this->neighborWeights)) {
            $this->neighborWeights[$neighbor] = 0;
        }

        // Set new weight only if it is larger
        /*if ($weight > $this->neighborWeights[$neighbor])
        {
            $this->neighborWeights[$neighbor] = $weight;
        }*/
        $this->neighborWeights[$neighbor] += $weight;
    }

    /**
     * @return float
     */
    public function getTotalNeighborWeight()
    {
        $result = 0.0;

        foreach ($this->neighborWeights as $weight) {
            $result += $weight;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getNeighborPercentages()
    {
        $total = $this->getTotalNeighborWeight();

        // Clone array
        $neighborPercentages = array_merge($this->neighborWeights);

        foreach ($neighborPercentages as $key => $percentage) {
            $neighborPercentages[$key] = max(($percentage/(float)$total) * 100.0, self::BASE_CHANCE);
        }

        return $neighborPercentages;
    }

    /**
     * @return int|string
     */
    public function getBiasedNeighborChoice()
    {
        if (count($this->neighborWeights) == 1) {
            return array_keys($this->neighborWeights)[0];
        }

        $percentages = $this->getNeighborPercentages();

        return $this->getRandomWeightedElement($percentages);
    }

    /**
     * @param array $weightedValues
     * @return int|string
     */
    private function getRandomWeightedElement(array $weightedValues) {
        $rand = mt_rand(1, (int) array_sum($weightedValues));

        foreach ($weightedValues as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $key;
            }
        }
    }
} 