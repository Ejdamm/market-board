<?php


namespace MarketBoard\Controllers;

use MarketBoard\Locale;
use MarketBoard\Utils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

abstract class BaseController
{
    protected $container;
    protected $logger;
    protected $language;
    protected $db;
    protected $mailer;
    protected $session;
    protected $settings;
    private $locales;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get('logger');
        $this->language = $container->get('language');
        $this->db = $container->get('db');
        $this->mailer = $container->get('mailer');
        $this->session = $container->get('session');
        $this->settings = $container->get('settings');
        $this->locales = $this->createLocales($container->get('locales'), $this->language['code']);
    }

    private function createLocales($locales, $currentLocale)
    {
        $localeList = [];
        foreach ($locales as $locale) {
            $url = Utils::localizeUrl($_SERVER['REQUEST_URI'], $locale['code'], $currentLocale);
            $localeObject = new Locale($locale['code'], $locale['language'], $url);
            $localeList[] = $localeObject->toArray();
        }
        return $localeList;
    }

    abstract public function get($request, $response, $args) : ResponseInterface;

    abstract public function post($request, $response, $args) : ResponseInterface;

    protected function render($response, $template, $args = [])
    {
        return $this->container->get('view')->render($response, $template, array_merge($args, [
            'language' => $this->language,
            'settings' => $this->settings,
            'locales' => $this->locales,
        ]));
    }

    protected function render404($response)
    {
        return $this->render($response->withStatus(501), 'errors/error404.html.twig');
    }

    protected function render501($response)
    {
        return $this->render($response->withStatus(501), 'errors/error501.html.twig');
    }
}
