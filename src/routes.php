<?php

use MarketBoard\Controllers;

$app->get('/listings/new', Controllers\NewListingController::class . ':get')->setName('new_listing');
$app->post('/listings/new', Controllers\NewListingController::class . ':post');
$app->get('/listings[/]', Controllers\ListingsController::class . ':get')->setName('listings');
$app->get('/selling', Controllers\SellingController::class . ':get')->setName('selling');
$app->get('/buying', Controllers\BuyingController::class . ':get')->setName('buying');
$app->get('/listings/{id}', Controllers\SingleListingController::class . ':get')->setName('single_listing');
$app->post('/listings/{id}', Controllers\SingleListingController::class . ':post');
$app->get('/', Controllers\HomepageController::class . ':get')->setName('homepage');
