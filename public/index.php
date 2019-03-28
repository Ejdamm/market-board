<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Mos\Database\CDatabaseBasic as Database;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';

$app = new \Slim\App(['settings' => $config]);

$container = $app->getContainer();
$container['db'] = function ($c) {
    $conf = $c['settings']['db'];
    $db = new Database(['dsn' => 'mysql:host=' . $conf['host'] . ';dbname=' . $conf['dbname'],
        'username' => $conf['user'], 'password' => $conf['pass']]);
    $db->connect();
    return $db;
};

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views/', [
        'cache' => false
    ]);
    $router = $container->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    return $view;
};

// Register routes
require __DIR__ . '/../src/routes.php';

$app->run();
