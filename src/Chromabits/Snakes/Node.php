<?php

namespace Chromabits\Snakes;

use SplFixedArray;
use Exception;

/**
 * Class Node
 *
 * @package src\Chromabits\Snakes
 */
class Node
{
    /**
     * @var int
     */
    protected $dimensions;

    /**
     * @var SplFixedArray
     */
    protected $identifier;

    public function __construct($dimensions = 3, $identifier = null)
    {
        if ($dimensions < 3) {
            throw new Exception('Min dimension is 3');
        }

        $this->dimensions = $dimensions;

        if (!is_null($identifier)) {
            $this->parseIdentifier($identifier);
        } else {
            $this->setupIdentifier();
        }
    }

    /**
     * Initialize the identifier array
     */
    protected function setupIdentifier()
    {
        $this->identifier = new SplFixedArray($this->dimensions);

        for ($i = 0; $i < $this->dimensions; $i++)
        {
            $this->identifier[$i] = 0;
        }
    }

    /**
     * Parse a string into an identifier
     *
     * @param $identifier
     * @throws \Exception
     */
    protected function parseIdentifier($identifier)
    {
        if (strlen($identifier) != $this->dimensions) {
            throw new \Exception('Dimension of identifier string does not match');
        }

        $chars = str_split($identifier);
        $finalIdentifier = new SplFixedArray($this->dimensions);

        for ($i = 0; $i < $this->dimensions; $i++)
        {
           $finalIdentifier[$i] = (int) $chars[$i];
        }

        $this->identifier = $finalIdentifier;
    }

    /**
     * Check whether another node is near by the their identifier
     *
     * @param Node $otherNode
     * @return bool
     */
    public function isNear(Node $otherNode)
    {
        $otherIdentifier = $otherNode->getIdentifier();

        if ($otherIdentifier->getSize() != $this->identifier->getSize()) {
            return false;
        }

        $differences = 0;

        for ($i = 0; $i < $this->dimensions; $i++) {
            if ($otherIdentifier[$i] != $this->identifier[$i]) {
                $differences++;
            }

            if ($differences > 1) {
                return false;
            }
        }

        return true;
    }

    public function computeNeighbors()
    {
        $neighbors = array();

        for ($i = 0; $i < $this->dimensions; $i++) {
            $neighborId = clone $this->identifier;

            if ($neighborId[$i] == 1) {
                $neighborId[$i] = 0;
            } else {
                $neighborId[$i] = 1;
            }

            $neighbors[] = implode('', $neighborId->toArray());
         }

        return $neighbors;
    }

    /**
     * @return string
     */
    public function getStringIdentifier()
    {
        return implode('', $this->getIdentifier()->toArray());
    }

    /**
     * @return \SplFixedArray
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return int
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }
} 