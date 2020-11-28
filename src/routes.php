<?php

use MarketBoard\Controllers;

$app->get('/listings/new', Controllers\NewListingController::class . ':get');
$app->post('/listings/new', Controllers\NewListingController::class . ':post');
$app->get('/listings[/]', Controllers\MultipleListingsController::class . ':get')->setName('listings');
$app->get('/listings/{id}', Controllers\ShowSingleListingController::class . ':get');
$app->post('/listings/{id}', Controllers\ShowSingleListingController::class . ':post');
$app->get('/', Controllers\HomepageController::class . ':get')->setName('homepage');
