<?php

namespace Startplats\Tests\Functional;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Exception\MethodNotAllowedException;
use Slim\Exception\NotFoundException;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

/**
 * Class created by slims framework
 *
 * This is an example class that shows how you could set up a method that
 * runs the application. Note that it doesn't cover all use-cases and is
 * tuned to the specifics of this skeleton app, so if your needs are
 * different, you'll need to change it.
 */
class BaseTestCase extends TestCase // https://github.com/symfony/symfony/issues/21816
{
    private $logFile;

    private static $container;

    public function __construct(string $logFile = "logs/apptest.log")
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
     * @return ResponseInterface
     * @throws MethodNotAllowedException
     * @throws NotFoundException
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
        $config = include __DIR__ . '/../../config/config.php';
        $app = new App($config);

        self::$container =  $app->getContainer();
        self::$container['db'] = function ($container) {
            $default = $container->get('environments')['default_database'];
            $conf = $container->get('environments')[$default];
            $pdo = new PDO($conf['adapter'] . ':host=' . $conf['host'] . ';dbname=' . $conf['name'],
                $conf['user'], $conf['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        };
        $this->prepareDatabase();

        self::$container['view'] = function ($container) {
            $view = new Twig(__DIR__ . '/../../resources/views/', [
                'cache' => false
            ]);
            $router = $container->get('router');
            $uri = Uri::createFromEnvironment(new Environment($_SERVER));
            $view->addExtension(new TwigExtension($router, $uri));
            return $view;
        };

        self::$container['logger'] = function ($c) {
            $logger = new Logger('functional_test');
            $file_handler = new StreamHandler('logs/apptest.log');
            $logger->pushHandler($file_handler);
            return $logger;
        };

        // To make sure the log is empty at the start of the run
        $this->clearLog();

        // Register routes
        require __DIR__ . '/../../src/routes.php';

        // Process the application
        $response = $app->process($request, $response);

        // Return the response
        return $response;
    }

    public function prepareDatabase()
    {
        //stuff..
    }

    public function clearDatabaseOf($table, $data)
    {
        $index = 0;
        $conditions = "";
        foreach ($data as $value => $key) {
            $conditions .= "$value='$key'";
            $index ++;
            if ($index != sizeof($data)) {
                $conditions .= " AND ";
            }
        }
        self::$container['db']->query("DELETE FROM $table WHERE $conditions;");
    }

    public function verifyEntryInserted($table, $data)
    {
        $index = 0;
        $conditions = "";
        foreach ($data as $value => $key) {
            $conditions .= "$value='$key'";
            $index ++;
            if ($index != sizeof($data)) {
                $conditions .= " AND ";
            }
        }
        $statement = self::$container['db']->query("SELECT * FROM $table WHERE $conditions;");
        $queryResult = $statement->fetch();

        // False if no data was found. An object full of data if found.
        $this->assertNotFalse($queryResult, "Sql query did not find a result from given data!");
    }

    /**
     * Verify the content of logFile,
     *
     * @param array $expectedContent Array of strings with expected Strings to contain.
     */
    public function assertLogContains($expectedContent = array())
    {
        $actualLogging = file_get_contents($this->logFile);

        foreach ($expectedContent as $singleContent) {
            echo $singleContent;
        }
    }

    /**
     * Verify the log file does not contain the given array
     * @param array $expectedNotContain Array of strings with expected strings to NOT contain.
     */
    public function assertLogDoesNotContain($expectedNotContain = array())
    {
        $actualLogging = file_get_contents($this->logFile);

        foreach ($expectedNotContain as $singleContent) {
            $this->assertStringNotContainsString($singleContent, $actualLogging);
        }
    }

    /**
     * Clear the content of target log file
     */
    public function clearLog()
    {
        file_put_contents($this->logFile, "");
    }

    public function clearDatabase()
    {
    }
}
