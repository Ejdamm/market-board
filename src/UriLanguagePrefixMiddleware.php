<?php

namespace MarketBoard;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Checks if Uri has invalid or missing language prefix, and redirects to valid route
 * @package App\Middleware
 */
class UriLanguagePrefixMiddleware
{

    private $router;
    private $session;
    private $locales;
    private $logger;
    private $settings;

    public function __construct($container)
    {
        $this->router = $container->get('router');
        $this->session =  $container->get('session');
        $this->locales = $container->get('locales');
        $this->logger = $container->get('logger');
        $this->settings = $container->get('settings');
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        $route = $request->getAttribute('route');
        if ($route == null) {
            $routeName = 'homepage';
            $requestedLocale = null;
        } else {
            $routeName = $route->getName();
            $requestedLocale = explode('/', $route->getArgument('lang'))[0];
        }

        if ($requestedLocale == null || !in_array($requestedLocale, array_column($this->locales, 'code'))) {
            $locale = $this->session->get('language', $this->settings['defaultLocale']);
            return $response->withRedirect($this->router->pathFor($routeName, [
                'lang' => $locale,
            ]));
        }

        $this->session->set('language', $requestedLocale);
        $response = $next($request, $response);

        return $response;
    }
}