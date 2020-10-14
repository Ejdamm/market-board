<?php

use Startplats\Controllers;

$app->get('/listings/new', Controllers\NewListingController::class . ':get');
$app->post('/listings/new', Controllers\NewListingController::class . ':post');
$app->get('/[listings/]', Controllers\ShowMultipleListingsController::class . ':get')->setName('homepage');
$app->get('/listings/{id}', Controllers\ShowSingleListingController::class . ':get');
$app->post('/listings/{id}', Controllers\ShowSingleListingController::class . ':post');
