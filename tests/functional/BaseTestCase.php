<?php

namespace Startplats\Tests\Functional;

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Environment;

/**
 * Class created by slims framework
 *
 * This is an example class that shows how you could set up a method that
 * runs the application. Note that it doesn't cover all use-cases and is
 * tuned to the specifics of this skeleton app, so if your needs are
 * different, you'll need to change it.
 */
class BaseTestCase extends \PHPUnit\Framework\TestCase // https://github.com/symfony/symfony/issues/21816
{
    private $logFile;
    public function __construct(String $logFile = "logs/app.log")
    {
        //TODO: Should we always use a test log?
        parent::__construct();
        $this->logFile = $logFile;
    }

    /**
     * Process the application given a request method and URI
     *
     * @param string $requestMethod the request method (e.g. GET, POST, etc.)
     * @param string $requestUri the request URI
     * @param array|object|null $requestData the request data
     * @return \Slim\Http\Response
     */
    public function runApp($requestMethod, $requestUri, $requestData = null)
    {
        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => $requestMethod,
                'REQUEST_URI' => $requestUri
            ]
        );

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        // Add request data, if it exists
        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }

        // Set up a response object
        $response = new Response();

        // Instantiate the application
        $app = new App();

        $container = $app->getContainer();
        $container['db'] = function ($c) {
            $conf = $c['settings']['db'];
            $db = new Database(['dsn' => 'mysql:host=' . $conf['host'] . ';dbname=' . $conf['dbname'],
                'username' => $conf['user'], 'password' => $conf['pass']]);
            $db->connect();
            return $db;
        };

        $container['view'] = function ($container) {
            $view = new \Slim\Views\Twig(__DIR__ . '/../../resources/views/', [
                'cache' => false
            ]);
            $router = $container->get('router');
            $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
            $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
            return $view;
        };

        $container['logger'] = function ($c) {
            $logger = new \Monolog\Logger('functional_test');
            $file_handler = new \Monolog\Handler\StreamHandler('logs/apptest.log');
            $logger->pushHandler($file_handler);
            return $logger;
        };

        // Register routes
        require __DIR__ . '/../../src/routes.php';

        // Process the application
        $response = $app->process($request, $response);

        // Return the response
        return $response;
    }

    /**
     * Clear the content of target log file
     * @param String $logName Full path to logfile
     */
    public function clearLog($logName)
    {
        file_put_contents($logName, "");
    }
}
