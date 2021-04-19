<?php

use MarketBoard\Controllers;
use MarketBoard\UriLanguagePrefixMiddleware;

$app->get('/', Controllers\HomepageController::class . ':get');


$container = $app->getContainer();
$app->group('/{lang}', function() use($container) {
    $this->get('/', Controllers\HomepageController::class . ':get')->setName('homepage');
    $this->get('/listings/new', Controllers\NewListingController::class . ':get')->setName('new_listing');
    $this->post('/listings/new', Controllers\NewListingController::class . ':post');
    $this->get('/listings[/]', Controllers\ListingsController::class . ':get')->setName('listings');
    $this->get('/selling', Controllers\SellingController::class . ':get')->setName('selling');
    $this->get('/buying', Controllers\BuyingController::class . ':get')->setName('buying');
    $this->get('/listings/{id}', Controllers\SingleListingController::class . ':get')->setName('single_listing');
    $this->post('/listings/{id}', Controllers\SingleListingController::class . ':post');
})->add(new UriLanguagePrefixMiddleware($container));
