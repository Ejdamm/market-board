<?php


namespace MarketBoard\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

abstract class BaseController
{
    protected $container;
    protected $view;
    protected $logger;
    protected $language;
    protected $db;
    protected $mailer;
    protected $session;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->view = $container->get('view');
        $this->logger = $container->get('logger');
        $this->language = $container->get('language');
        $this->db = $container->get('db');
        $this->mailer = $container->get('mailer');
        $this->session = $container->get('session');
    }

    abstract public function get($request, $response, $args) : ResponseInterface;

    abstract public function post($request, $response, $args) : ResponseInterface;
}
