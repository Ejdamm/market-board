<?php

use MarketBoard\Controllers;

$app->get('/listings/new', Controllers\NewListingController::class . ':get');
$app->post('/listings/new', Controllers\NewListingController::class . ':post');
$app->get('/listings[/]', Controllers\MultipleListingsController::class . ':get')->setName('listings');
$app->get('/listings/{id}', Controllers\SingleListingController::class . ':get');
$app->post('/listings/{id}', Controllers\SingleListingController::class . ':post');
$app->get('/', Controllers\HomepageController::class . ':get')->setName('homepage');
