<?php


namespace MarketBoard\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class HomepageController extends BaseController
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function get($request, $response, $args) : ResponseInterface
    {
        return $this->render($response, 'homepage.html.twig');
    }

    public function post($request, $response, $args): ResponseInterface
    {
        $this->logger->addWarning(get_class($this) . " POST not implemented");
        return $this->render501($response);
    }
}
