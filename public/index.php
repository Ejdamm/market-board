<?php

use Anddye\Mailer\Mailer;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slim\App;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Uri;
use Slim\Middleware\Session;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use SlimSession\Helper;
use Twig\TwigFilter;

require __DIR__ . '/../vendor/autoload.php';

$config = include __DIR__ . '/../config/config.php';
$app = new App($config);

$app->add(new Session([
    'name' => 'my_session',
    'autorefresh' => true
]));


$container = $app->getContainer();

unset($container['notFoundHandler']);
$container['notFoundHandler'] = function (Container $container) {
    return function ($request, $response) use ($container) {
        return $container['view']->render($response->withStatus(404), 'errors/error404.html.twig', [
            "language" => $container['language'],
            "settings" => $container['settings'],
        ]);
    };
};

$container['db'] = function (Container $container) {
    $conf = $container->get('settings')['db'];
    $pdo = new PDO(
        $conf['adapter'] . ':host=' . $conf['host'] . ';dbname=' . $conf['name'],
        $conf['user'],
        $conf['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
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

$container['mailer'] = function (Container $container) {
    $conf = $container->get('settings')['email'];
    $mailer = new Mailer($container['view'], [
        'host'      => $conf['smtp']['host'],
        'port'      => $conf['smtp']['port'],
        'username'  => $conf['smtp']['username'],
        'password'  => $conf['smtp']['password'],
        'protocol'  => $conf['smtp']['protocol']
    ]);
    $mailer->setDefaultFrom($conf['from'], $conf['name']);

    return $mailer;
};

$container['session'] = function (Container $container) {
    return new Helper;
};

$container['language'] = function (Container $container) {
    // Set default language
    $language_code = $container['session']->get('language', $container->get('settings')['defaultLocale']);
    $query = "SELECT * FROM language WHERE code = ?;";
    $statement = $container['db']->prepare($query);
    $statement->execute([$language_code]);
    $language = $statement->fetch();
    return $language;
};

$container['locales'] = function (Container $container) {
    $query = "SELECT language as language, code as code FROM language;";
    $statement = $container['db']->prepare($query);
    $statement->execute();
    $locales = $statement->fetchAll();
    return $locales;
};

$container['errorHandler'] = function ($container) {
    return function ($request, $response, $exception) use ($container) {
        $container->logger->addError("Exception thrown. Stacktrace: " . $exception);
        return $container->view->render($response->withStatus(500), 'errors/error500.html.twig', [
            'language' => $container->language,
            'settings' => $container->settings,
        ]);
    };
};

$container['phpErrorHandler'] = function ($container) {
    return function ($request, $response, $exception) use ($container) {
        $container->logger->addError("Exception thrown. Stacktrace: " . $exception);
        return $container->view->render($response->withStatus(500), 'errors/error500.html.twig', [
            'language' => $container->language,
            "settings" => $container->settings,
        ]);
    };
};

// Register routes
require __DIR__ . '/../src/routes.php';

$app->run();
