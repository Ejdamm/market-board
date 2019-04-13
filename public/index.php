<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Mos\Database\CDatabaseBasic;
use Slim\App;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Uri;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Twig\TwigFilter;

require __DIR__ . '/../vendor/autoload.php';

$config = include __DIR__ . '/../config/config.php';
$app = new App($config);

$container = $app->getContainer();
$container['db'] = function (Container $container) {
    $default = $container->get('environments')['default_database'];
    $conf = $container->get('environments')[$default];
    $db = new CDatabaseBasic(['dsn' => $conf['adapter'] . ':host=' . $conf['host'] . ';dbname=' . $conf['name'],
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
    $filter = new TwigFilter('castToArray', function ($stdClassObject) {
        $response = array();
        if ($stdClassObject) {
            foreach ($stdClassObject as $key => $value) {
                $response[] = $value;
            }
        }
        return $response;
    });
    $view->getEnvironment()->addFilter($filter);
    return $view;
};

$container['logger'] = function (Container $container) {
    $conf = $container->get('settings')['logger'];
    $logger = new Logger($conf['name']);
    $fileHandler = new StreamHandler($conf['path'], $conf['level']);
    $logger->pushHandler($fileHandler);
    return $logger;
};


// Register routes
require __DIR__ . '/../src/routes.php';

$app->run();
