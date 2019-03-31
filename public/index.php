<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use \Mos\Database\CDatabaseBasic;
use Slim\App;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Uri;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

require __DIR__ . '/../vendor/autoload.php';

$config = include __DIR__ . '/../config/config.php';
$app = new App(['settings' => $config['settings']]);

$container = $app->getContainer();
$container['db'] = function (Container $container) {
    $conf = $container->get('settings')['db'];
    $db = new CDatabaseBasic(['dsn' => 'mysql:host=' . $conf['host'] . ';dbname=' . $conf['dbname'],
        'username' => $conf['user'], 'password' => $conf['pass']]);
    $db->connect();
    return $db;
};

$container['view'] = function (Container $container) {
    $view = new Twig(__DIR__ . '/../resources/views/', [
        'cache' => false
    ]);
    $router = $container->get('router');
    $uri = Uri::createFromEnvironment(new Environment($_SERVER));
    $view->addExtension(new TwigExtension($router, $uri));
    return $view;
};

$container['logger'] = function (Container $container) {
    $conf = $container->get('settings')['logger'];
    $logger = new Logger($conf['name']);
    $file_handler = new StreamHandler($conf['path'], $conf['level']);
    $logger->pushHandler($file_handler);
    return $logger;
};


// Register routes
require __DIR__ . '/../src/routes.php';

$app->run();
