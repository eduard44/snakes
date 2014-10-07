# Snake-in-the-box

PHP implementation of the Snake-in-the-box AI problem

## Setup

1.- Setup composer and download dependencies:

`composer update`

2.- Test that it works:

`php run.php`

## Example:

Show path for hypercube (4 dimensions):

`php run.php snakes -d 4 -p`

## Randomized Example:

Show approximate longest path for dimension 7 (using 10000000 iterations):

`php run.php snakes -d 7 -r -i 10000000 -vv`

## New heuristic (Guided search):

`php run.php snakes:guided -d 7 -i 100000`