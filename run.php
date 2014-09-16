<?php

// Load composer autoloader
require_once 'vendor/autoload.php';

// Create app
$app = new \Symfony\Component\Console\Application('snakes', '0.0.1');

// Setup available commands
$app->add(new \Chromabits\Snakes\SnakesCommand());

// Execute app
$app->run();