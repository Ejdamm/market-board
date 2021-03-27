<?php


namespace MarketBoard\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class BuyingController extends ListingsController
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->type = 'buying';
    }

    public function get($request, $response, $args): ResponseInterface
    {
        $this->listings->addWhereFilter($this->listings->TYPE_FIELD, $this->type);
        return parent::get($request, $response, $args);
    }
}
