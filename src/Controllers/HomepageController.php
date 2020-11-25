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
        return $this->view->render($response, 'homepage.html.twig', [
            'language' => $this->language,
        ]);
    }

    public function post($request, $response, $args): ResponseInterface
    {
        $this->logger->addError("/ POST not implemented");
        return $this->view->render($response->withStatus(501), 'errors/error501.html.twig', [
            'language' => $this->language,
        ]);
    }
}
